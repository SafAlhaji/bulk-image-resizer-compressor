# Bulk Image Resizer and Compressor

This repository contains a web application designed to resize and compress images in bulk. The application accepts an Excel file containing multiple image links, processes each image according to user-defined options, and provides a new Excel file with links to the processed images. The project is built with Laravel (backend) and Vue.js (frontend).

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Setup and Installation](#setup-and-installation)
  - [Laravel Backend](#laravel-backend)
  - [Vue.js Frontend](#vuejs-frontend)
- [Usage](#usage)
- [License](#license)

## Overview

The Bulk Image Resizer and Compressor application simplifies the process of resizing and compressing multiple images. By uploading an Excel file containing image URLs, users can batch process these images based on specified options, and download a new Excel file with links to the optimized images.

## Features

- Upload an Excel file with image URLs.
- Define options for resizing and compressing images.
- Batch process images using the specified options.
- Download an Excel file containing links to the processed images.
- Real-time progress updates.

## Setup and Installation

### Laravel Backend

1. **Clone the repository:**
    ```bash
    git clone https://github.com/SafAlhaji/bulk-image-resizer-compressor.git
    cd bulk-image-resizer-compressor
    ```

2. **Install the PHP dependencies:**
    ```bash
    composer install
    ```

3. **Set up environment variables:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Edit the `.env` file to configure your database and other settings.

4. **Run database migrations:**
    ```bash
    php artisan migrate
    ```

5. **Start the Laravel development server:**
    ```bash
    php artisan serve
    ```

### Vue.js Frontend

1. **Navigate to the frontend directory:**
    ```bash
    cd frontend
    ```

2. **Install the Node.js dependencies:**
    ```bash
    npm install
    ```

3. **Compile the frontend assets:**
    ```bash
    npm run dev
    ```
    This will start the frontend development server, typically accessible at `http://localhost:8080`.

## Usage

1. **Ensure the Laravel backend server is running.**
2. **Start the Vue.js frontend development server.**
3. **Open a web browser and navigate to `http://localhost:8080`.**
4. **Upload an Excel file with image URLs.**
5. **Define the resizing and compression options.**
6. **Submit the form to process the images.**
7. **Download the resulting Excel file with links to the processed images.**


## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for more details.
