<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subtitle Translator - Ollama</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
        }

        .ollama-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 10px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 1rem;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-input {
            width: 100%;
            padding: 15px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            background-color: #f8f9fa;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            color: #666;
        }

        .file-input:hover {
            border-color: #f093fb;
            background-color: #fef5ff;
        }

        .file-input input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-info {
            margin-top: 10px;
            padding: 10px;
            background-color: #e8f5e8;
            border-radius: 5px;
            color: #2d5a2d;
            display: none;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            background-color: white;
        }

        select:focus, input[type="text"]:focus {
            outline: none;
            border-color: #f093fb;
            box-shadow: 0 0 0 3px rgba(240, 147, 251, 0.1);
        }

        .translate-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }

        .translate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(240, 147, 251, 0.3);
        }

        .translate-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        .progress-container {
            margin-top: 10px;
            display: none;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e1e1e1;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            margin-top: 5px;
            font-size: 0.9rem;
            color: #666;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .result-container {
            margin-top: 30px;
            display: none;
        }

        .result-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .download-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .download-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }

        .result-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 0 0 10px 10px;
            border: 1px solid #e1e1e1;
            border-top: none;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
            line-height: 1.6;
            max-height: 400px;
            overflow-y: auto;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
        }

        .back-link a {
            color: #f093fb;
            text-decoration: none;
            padding: 10px 20px;
            border: 2px solid #f093fb;
            border-radius: 10px;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .back-link a:hover {
            background-color: #f093fb;
            color: white;
            transform: translateY(-2px);
        }

        .language-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .language-option {
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            background: white;
        }

        .language-option:hover {
            border-color: #f093fb;
            background-color: #fef5ff;
        }

        .language-option.selected {
            border-color: #f093fb;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌍 Subtitle Translator</h1>
            <p>Upload your SRT file and translate it to any language with AI</p>
            <div class="ollama-badge">🤖 Powered by Local Ollama AI</div>
        </div>

        <form id="translateForm" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="file">Upload SRT File</label>
                <div class="file-input-wrapper">
                    <div class="file-input">
                        <input type="file" id="file" name="file" accept=".srt,.txt" required>
                        <span>📁 Click to upload or drag and drop your SRT file</span>
                    </div>
                    <div class="file-info" id="fileInfo"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="target_language">Target Language</label>
                <input type="text" id="target_language" name="target_language" placeholder="e.g., Spanish, French, German, Swahili..." required>
                
                <div class="language-grid">
                    <div class="language-option" data-lang="Spanish">🇪🇸 Spanish</div>
                    <div class="language-option" data-lang="French">🇫🇷 French</div>
                    <div class="language-option" data-lang="German">🇩🇪 German</div>
                    <div class="language-option" data-lang="Italian">🇮🇹 Italian</div>
                    <div class="language-option" data-lang="Portuguese">🇵🇹 Portuguese</div>
                    <div class="language-option" data-lang="Chinese">🇨🇳 Chinese</div>
                    <div class="language-option" data-lang="Japanese">🇯🇵 Japanese</div>
                    <div class="language-option" data-lang="Korean">🇰🇷 Korean</div>
                    <div class="language-option" data-lang="Arabic">🇸🇦 Arabic</div>
                    <div class="language-option" data-lang="Russian">🇷🇺 Russian</div>
                    <div class="language-option" data-lang="Hindi">🇮🇳 Hindi</div>
                    <div class="language-option" data-lang="Swahili">🇰🇪 Swahili</div>
                </div>
            </div>

            <button type="submit" class="translate-btn" id="translateBtn">
                <span class="loading-spinner" id="loadingSpinner"></span>
                <span id="btnText">🚀 Translate with Ollama</span>
            </button>

            <div class="progress-container" id="progressContainer">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-text" id="progressText">Processing chunks...</div>
            </div>
        </form>

        <div class="error-message" id="errorMessage"></div>

        <div class="result-container" id="resultContainer">
            <div class="result-header">
                <span>✨ Translation Result</span>
                <button class="download-btn" id="downloadBtn" onclick="downloadTranslation()">
                    📥 Download .srt
                </button>
            </div>
            <div class="result-content" id="resultContent"></div>
        </div>

        <div class="back-link">
            <a href="/">← Back to Home</a>
        </div>
    </div>

    <script>
        // CSRF Token setup for AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // File input handling
        const fileInput = document.getElementById('file');
        const fileInfo = document.getElementById('fileInfo');
        const fileInputWrapper = document.querySelector('.file-input');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileInfo.style.display = 'block';
                fileInfo.innerHTML = `
                    <strong>📎 Selected:</strong> ${file.name}<br>
                    <strong>📏 Size:</strong> ${(file.size / 1024).toFixed(1)} KB<br>
                    <strong>📅 Type:</strong> ${file.type || 'SRT File'}
                `;
                fileInputWrapper.style.borderColor = '#28a745';
                fileInputWrapper.style.backgroundColor = '#e8f5e8';
            }
        });

        // Language selection
        const languageOptions = document.querySelectorAll('.language-option');
        const targetLanguageInput = document.getElementById('target_language');

        languageOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                languageOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Set the input value
                targetLanguageInput.value = this.getAttribute('data-lang');
            });
        });

        // Form submission
        const form = document.getElementById('translateForm');
        const translateBtn = document.getElementById('translateBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const btnText = document.getElementById('btnText');
        const errorMessage = document.getElementById('errorMessage');
        const resultContainer = document.getElementById('resultContainer');
        const resultContent = document.getElementById('resultContent');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');

        let currentTranslation = '';
        let currentFilename = '';

        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Hide previous results/errors
            errorMessage.style.display = 'none';
            resultContainer.style.display = 'none';
            progressContainer.style.display = 'none';

            // Show loading state
            translateBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            btnText.textContent = 'Translating with Ollama...';
            progressContainer.style.display = 'block';
            progressFill.style.width = '0%';
            progressText.textContent = 'Starting translation...';

            const formData = new FormData(form);

            try {
                // Simulate progress during translation
                const progressInterval = setInterval(() => {
                    const currentWidth = parseFloat(progressFill.style.width) || 0;
                    if (currentWidth < 90) {
                        progressFill.style.width = (currentWidth + Math.random() * 10) + '%';
                        progressText.textContent = 'Processing subtitle chunks...';
                    }
                }, 500);

                const response = await fetch('/ai-translate-ollama', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                clearInterval(progressInterval);
                progressFill.style.width = '100%';
                progressText.textContent = 'Translation complete!';

                const data = await response.json();

                if (data.success) {
                    // Store translation data
                    currentTranslation = data.translated_text;
                    currentFilename = data.original_filename || 'subtitles';

                    // Show result
                    resultContent.textContent = data.translated_text;
                    resultContainer.style.display = 'block';
                    
                    // Hide progress after a short delay
                    setTimeout(() => {
                        progressContainer.style.display = 'none';
                    }, 1000);
                    
                    // Scroll to result
                    resultContainer.scrollIntoView({ behavior: 'smooth' });
                } else {
                    // Show error
                    progressContainer.style.display = 'none';
                    errorMessage.textContent = data.error || 'An unexpected error occurred.';
                    errorMessage.style.display = 'block';
                }

            } catch (error) {
                // Show network error
                progressContainer.style.display = 'none';
                errorMessage.textContent = 'Network error: Unable to connect to the server or Ollama is not running.';
                errorMessage.style.display = 'block';
            } finally {
                // Reset button state
                translateBtn.disabled = false;
                loadingSpinner.style.display = 'none';
                btnText.textContent = '🚀 Translate with Ollama';
            }
        });

        // Download functionality
        async function downloadTranslation() {
            if (!currentTranslation || !currentFilename) {
                alert('No translation available to download.');
                return;
            }

            try {
                const formData = new FormData();
                formData.append('translated_text', currentTranslation);
                formData.append('filename', currentFilename);

                const response = await fetch('/download-translation', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (response.ok) {
                    // Create blob and download
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = currentFilename + '_translated.srt';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                } else {
                    alert('Download failed. Please try again.');
                }
            } catch (error) {
                alert('Download error: ' + error.message);
            }
        }

        // Drag and drop functionality
        const fileInputContainer = document.querySelector('.file-input');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileInputContainer.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileInputContainer.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileInputContainer.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            fileInputContainer.style.borderColor = '#f093fb';
            fileInputContainer.style.backgroundColor = '#fef5ff';
        }

        function unhighlight(e) {
            fileInputContainer.style.borderColor = '#ddd';
            fileInputContainer.style.backgroundColor = '#f8f9fa';
        }

        fileInputContainer.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        }
    </script>
</body>
</html>
