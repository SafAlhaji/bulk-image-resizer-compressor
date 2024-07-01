<template>
    <v-app>
        <v-main>
            <v-container fluid fill-height dark>
                <router-view></router-view>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
