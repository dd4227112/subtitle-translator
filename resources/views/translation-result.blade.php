<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Translation Result</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            max-width: 800px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .result-container {
            margin-bottom: 30px;
        }

        .result-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .result-content {
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 0 0 10px 10px;
            border: 1px solid #e1e1e1;
            border-top: none;
            white-space: pre-wrap;
            font-family: 'Courier New', monospace;
            line-height: 1.8;
            max-height: 500px;
            overflow-y: auto;
            color: #333;
        }

        .back-link {
            text-align: center;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            padding: 12px 24px;
            border: 2px solid #667eea;
            border-radius: 10px;
            display: inline-block;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 1rem;
        }

        .back-link a:hover {
            background-color: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }

            .result-content {
                padding: 15px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✨ Translation Complete</h1>
        </div>

        <div class="result-container">
            <div class="result-header">
                🌍 Translated Subtitles
            </div>
            <div class="result-content">{{ $translatedText }}</div>
        </div>
        
        <div class="back-link">
            <a href="/">← Back to Home</a>
        </div>
    </div>
</body>
</html>
