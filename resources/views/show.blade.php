<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Document</title>
</head>
<body>
    @include('header')



    <style>
    .upload-box {
        border: 2px dashed #ccc;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        background: #f8f9fa;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-box.dragover {
        background: #e9ecef;
        border-color: #0d6efd;
    }

    .upload-box i {
        font-size: 48px;
        color: #6c757d;
    }
    </style>

    <div class="container">
        <form id="propertyImageForm" enctype="multipart/form-data">
            <div class="upload-box" id="dropZone">
                <i class="fas fa-cloud-upload-alt"></i>
                <h4 class="mt-3">Drag & Drop Images Here</h4>
                <p>or</p>
                <input type="file" class="form-control d-none" id="images" name="images[]" multiple accept="image/*">
                <button type="button" class="btn btn-primary" onclick="document.getElementById('images').click()">
                    Browse Files
                </button>
            </div>
            
            <div id="imagePreview" class="row mt-4">
                <!-- Preview images will appear here -->
            </div>

            <button type="submit" class="btn btn-primary mt-3">Upload Images</button>
        </form>
    </div>

    <script>
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('images');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    // Highlight drop zone when dragging over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function preventDefaults (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function highlight(e) {
        dropZone.classList.add('dragover');
    }

    function unhighlight(e) {
        dropZone.classList.remove('dragover');
    }

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        imageInput.files = files;
        handleFiles(files);
    }

    function handleFiles(files) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        [...files].forEach(file => {
            if (!file.type.startsWith('image/')) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'col-md-3 mb-3';
                div.innerHTML = `
                    <div class="card">
                        <img src="${e.target.result}" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.col-md-3').remove()">Remove</button>
                        </div>
                    </div>
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });
    }

    // Handle selected files through the input
    imageInput.addEventListener('change', function(e) {
        handleFiles(e.target.files);
    });

    // Form submission code remains the same as before
    document.getElementById('propertyImageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('http://localhost:8001/property/upload_images', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Images uploaded successfully!');
                document.getElementById('imagePreview').innerHTML = '';
                this.reset();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error uploading images');
        });
    });
    </script>
    @include('footer')
    <!-- Footer area end -->
</body>
</html>