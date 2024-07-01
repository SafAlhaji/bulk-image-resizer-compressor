<template>
    <v-row align="center" justify="center">
        <VueFullScreenFileDrop @drop="onDrop" text="Select images to upload & compress." />
        <v-col>
            <div class="text-center">
                <img width="100" src="/images/takteek.png" class="mb-2" />
                <h2>Bulk Image Resizer <span class="overline">v1.2</span></h2>
            </div>

            <v-slide-y-transition tag="v-card">
            <v-card elevation="2" class="mt-3 mx-auto" width="850">
                <v-card-text class="pr-7 pl-7">
                    <v-row class="mt-2">
                        <v-text-field label="Width" class="mr-2" 
                            :disabled="started"
                            dense outlined min="0" :rules="number" v-model.number="width"></v-text-field>
                        x
                        <v-text-field label="Height" class="ml-2" 
                            :disabled="started"
                            dense outlined min="0" :rules="number" v-model.number="height"></v-text-field>
                    </v-row>

                    <v-row class="mt-1">
                        <v-slider label="Image quality" max="100" min="0" 
                            :disabled="started"
                            v-model="quality" thumb-label ticks></v-slider>
                    </v-row>

                    <v-row class="mt-1">
                        <v-checkbox label="Add white padding instead of stretching the image, when the target aspect ratio changes"
                            :disabled="started" v-model="padNotStretch"></v-checkbox>
                    </v-row>

                    <v-row class="mt-1">
                        <v-checkbox label="Output images as WebP"
                            :disabled="started" v-model="isWebp"></v-checkbox>
                    </v-row>

                    <v-row class="mt-1">
                        <v-file-input show-size label="Excel file" 
                            :disabled="started"
                            v-if="!filesDropped"
                            hint="The excel file should have image links in its first column, and start from its first row. Any additional cells will be ignored."
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" 
                            v-model="files"></v-file-input>
                        <span v-else>
                            <v-icon class="mr-1">mdi-attachment</v-icon>
                            {{ droppedFiles.length }} images dropped (<span class="font-italic text-danger" style="cursor: pointer" @click="droppedFiles = []; filesDropped = false">clear</span>)
                        </span>
                    </v-row>
                    <v-row class="mt-0" v-if="!filesDropped">
                        <p class="ml-8">...or drag & drop your images.</p>
                    </v-row>

                    <v-row class="mt-2">
                        <v-slide-y-transition tag="v-progress-linear">
                            <v-progress-linear
                                v-model="progress"
                                color="blue-grey darken-4"
                                height="15"
                                v-show="started"
                                striped query stream :buffer-value="progress"
                                >
                                <template v-slot:default>
                                    <strong>Converting {{ counter }} out of {{ total }}</strong>
                                </template>
                            </v-progress-linear>
                        </v-slide-y-transition>
                    </v-row>
                    <v-row class="mt-2">
                        <v-slide-y-transition tag="v-simple-table">
                            <v-simple-table class="fill-width" v-if="started" height="200px">
                                <template v-slot:default>
                                    <thead>
                                        <tr>
                                            <th>Before</th>
                                            <th>After</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="output.length == 0">
                                            <td colspan="2" class="text-center">
                                                No links yet!
                                            </td>
                                        </tr>
                                        <tr v-else v-for="(item, index) in output" :key="index">
                                            <td class="text-truncate"><a :href="item.before" target="_blank">{{ item.before }}</a></td>
                                            <td class="text-truncate"><a :href="item.after" target="_blank">{{ item.after }}</a></td>
                                        </tr>
                                    </tbody>
                                </template>
                            </v-simple-table>
                        </v-slide-y-transition>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn text @click="fire()" v-if="!started">Convert</v-btn>
                    <v-btn text @click="started = false" v-if="started" color="red">Cancel</v-btn>
                </v-card-actions>
            </v-card>
            </v-slide-y-transition>
        </v-col>

        <v-snackbar v-model="snackbar">
            {{ snackbarText }}
            <template v-slot:action="{ attrs }">
                <v-btn color="pink" text v-bind="attrs" @click="snackbar = true">
                    Close
                </v-btn>
            </template>
        </v-snackbar>
    </v-row>
</template>

<script>
    import readXlsxFile from 'read-excel-file'
    import writeXlsxFile from 'write-excel-file'
    import VueFullScreenFileDrop from 'vue-full-screen-file-drop';
    import 'vue-full-screen-file-drop/dist/vue-full-screen-file-drop.css';

    export default {
        data: () => ({
            // Compression props
            width: 800,
            height: 800,
            quality: 80,
            padNotStretch: false,
            isWebp: false,

            // Validation & misc
            number: [val => (val >= 0 && val !== null) || 'This should be non-negative number.'],
            snackbar: false,
            snackbarText: null,
            started: false,
            files: [],

            // Progress control
            progress: 80,
            counter: 1,
            total: 100,

            // Drag & drop functionality
            filesDropped: false,
            droppedFiles: [],

            // Excel output & schema
            output: [],
            outputExcelSchema: [
                { column: 'Before',     type: String,   value: object => object.before },
                { column: 'After',      type: String,   value: object => object.after  },
            ]
        }),

        methods: {
            fire() {
                if (this.filesDropped) {
                    this.fireUpload();
                    return;
                }

                if (this.files == null || this.files.length == 0) {
                    this.showSnackbar('Please choose a file.');
                    return;
                }

                this.started = true;
                this.output = [];
                this.setProgress(0, 0);

                readXlsxFile(this.files).then(async (rows) => {
                    this.setProgress(1, rows.length);
                    
                    // Send images to compression, one by one
                    var promises = [];
                    for (let row of rows) {
                        
                        // Catch "cancel" signal
                        if (!this.started)
                            return;

                        let url = encodeURI(row[0]);

                        promises.push(
                            await this.sendImage(url).then(response => {
                                this.output.push({
                                    before: row[0],
                                    after: response.data
                                });
                            }).catch(() => {
                                this.output.push({
                                    before: row[0],
                                    after: ''
                                });
                            }).finally(() => {
                                if (this.counter != rows.length)
                                    this.setProgress(this.counter + 1, rows.length);
                            })
                        );
                    }

                    this.outputExcelFileFromPromises(promises);                    
                });
            },

            async fireUpload() {
                if (this.droppedFiles == null || this.droppedFiles.length == 0) {
                    this.showSnackbar('Please choose at least one file.');
                    return;
                }

                this.started = true;
                this.output = [];
                this.setProgress(0, 0);
                var promises = [];

                for (let file of this.droppedFiles) {
                    promises.push(
                        await this.uploadImage(file).then(response => {
                            this.output.push({
                                before: file.name,
                                after: response.data
                            });
                        }).catch(() => {
                            this.output.push({
                                before: file.name,
                                after: ''
                            });
                        }).finally(() => {
                            if (this.counter != this.droppedFiles.length)
                                this.setProgress(this.counter + 1, this.droppedFiles.length);
                        })
                    );
                }

                this.outputExcelFileFromPromises(promises);
                this.droppedFiles = [];
                this.filesDropped = false;              
            },

            outputExcelFileFromPromises(promises) {
                // Write all compressed image links to a new excel file
                Promise.all(promises).then(async () => {
                    await writeXlsxFile(this.output, {
                        schema: this.outputExcelSchema,
                        fileName: 'Compressed Images.xlsx'
                    }).then(() => {
                        this.started = false;
                    });
                });
            },

            async sendImage(url) {
                return await window.axios.post('/api/compress', {
                    url,
                    width: this.width,
                    height: this.height,
                    quality: this.quality,
                    padNotStretch: this.padNotStretch,
                    isWebp: this.isWebp
                });
            },

            async uploadImage(image) {
                const formData = new FormData();
                formData.append('image', image);
                formData.append('width', this.width);
                formData.append('height', this.height);
                formData.append('quality', this.quality);
                formData.append('padNotStretch', this.padNotStretch ? 1 : 0);
                formData.append('isWebp', this.isWebp ? 1 : 0);

                return await window.axios.post('/api/compress/upload', formData);
            },

            onDrop(formData, files) {
                this.filesDropped = true;
                this.droppedFiles.push(...files);
            },

            setProgress(current, total) {
                this.total = total;
                this.progress = Math.floor((current / total) * 100);
                this.counter = current;
            },

            showSnackbar(message) {
                this.snackbar = true;
                this.snackbarText = message;
            }
        },

        components: {
            VueFullScreenFileDrop,
        }
    }
</script>

<style>
    .hidden {
        visibility: hidden;
    }

    .visible {
        visibility: visible;
    }

    .fill-width {
        width: 100%;
    }

    .v-input--checkbox .v-label {
        margin-bottom: 0 !important;
    }

    .v-input--selection-controls {
        margin-top: 0 !important;
    }
</style>