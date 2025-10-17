<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversion PDF vers Word</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .conversion-area {
            border: 2px dashed #ddd;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            border-radius: 10px;
            background-color: #fafafa;
        }
        .file-input {
            margin: 20px 0;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-success:hover {
            background-color: #1e7e34;
        }
        .progress {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
            display: none;
        }
        .progress-bar {
            height: 100%;
            background-color: #007bff;
            width: 0%;
            transition: width 0.3s ease;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .feature {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        .feature-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #007bff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔄 Conversion PDF vers Word</h1>
        <p>Convertissez vos documents PDF en format Word éditable</p>
    </div>

    <div class="conversion-area">
        <div class="feature-icon">📄➡️📝</div>
        <h3>Glissez-déposez votre fichier PDF ici</h3>
        <p>ou cliquez pour sélectionner un fichier</p>

        <input type="file" id="pdfFile" accept=".pdf" class="file-input" style="display: none;">
        <button class="btn" onclick="document.getElementById('pdfFile').click()">
            Choisir un fichier PDF
        </button>
    </div>

    <div class="progress" id="progressContainer">
        <div class="progress-bar" id="progressBar"></div>
    </div>

    <div class="result" id="result"></div>

    <button class="btn btn-success" id="convertBtn" style="display: none;" onclick="convertPdfToWord()">
        🔄 Convertir en Word
    </button>

    <div class="features">
        <div class="feature">
            <div class="feature-icon">⚡</div>
            <h4>Conversion Rapide</h4>
            <p>Conversion en quelques secondes</p>
        </div>
        <div class="feature">
            <div class="feature-icon">🎯</div>
            <h4>Haute Précision</h4>
            <p>Préservation de la mise en forme</p>
        </div>
        <div class="feature">
            <div class="feature-icon">🔒</div>
            <h4>Sécurisé</h4>
            <p>Vos fichiers sont protégés</p>
        </div>
        <div class="feature">
            <div class="feature-icon">📱</div>
            <h4>Compatible</h4>
            <p>Fonctionne sur tous les appareils</p>
        </div>
    </div>
</div>

<script>
    let selectedFile = null;

    document.getElementById('pdfFile').addEventListener('change', function(e) {
        selectedFile = e.target.files[0];
        if (selectedFile) {
            document.querySelector('.conversion-area h3').textContent = `Fichier sélectionné: ${selectedFile.name}`;
            document.getElementById('convertBtn').style.display = 'inline-block';
        }
    });

    async function convertPdfToWord() {
        if (!selectedFile) {
            showResult('Veuillez sélectionner un fichier PDF', 'error');
            return;
        }

        const progressContainer = document.getElementById('progressContainer');
        const progressBar = document.getElementById('progressBar');
        const convertBtn = document.getElementById('convertBtn');

        // Afficher la barre de progression
        progressContainer.style.display = 'block';
        convertBtn.disabled = true;
        convertBtn.textContent = 'Conversion en cours...';

        // Simuler la progression
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 200);

        try {
            const formData = new FormData();
            formData.append('pdf_file', selectedFile);

            const response = await fetch('/convert-pdf-to-word/{{ $id ?? 1 }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Authorization': `Bearer ${localStorage.getItem('access_token')}`
                }
            });

            clearInterval(progressInterval);
            progressBar.style.width = '100%';

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = selectedFile.name.replace('.pdf', '.docx');
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);

                showResult('✅ Conversion réussie! Le téléchargement a commencé.', 'success');
            } else {
                throw new Error('Erreur lors de la conversion');
            }
        } catch (error) {
            clearInterval(progressInterval);
            showResult('❌ Erreur lors de la conversion: ' + error.message, 'error');
        } finally {
            setTimeout(() => {
                progressContainer.style.display = 'none';
                progressBar.style.width = '0%';
                convertBtn.disabled = false;
                convertBtn.textContent = '🔄 Convertir en Word';
            }, 1000);
        }
    }

    function showResult(message, type) {
        const result = document.getElementById('result');
        result.textContent = message;
        result.className = `result ${type}`;
        result.style.display = 'block';

        setTimeout(() => {
            result.style.display = 'none';
        }, 5000);
    }

    // Drag and drop functionality
    const conversionArea = document.querySelector('.conversion-area');

    conversionArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        conversionArea.style.backgroundColor = '#e3f2fd';
    });

    conversionArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        conversionArea.style.backgroundColor = '#fafafa';
    });

    conversionArea.addEventListener('drop', (e) => {
        e.preventDefault();
        conversionArea.style.backgroundColor = '#fafafa';

        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type === 'application/pdf') {
            selectedFile = files[0];
            document.querySelector('.conversion-area h3').textContent = `Fichier sélectionné: ${selectedFile.name}`;
            document.getElementById('convertBtn').style.display = 'inline-block';
        } else {
            showResult('Veuillez déposer un fichier PDF valide', 'error');
        }
    });
</script>
</body>
</html>
