<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skins Yükleme Paneli</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="color"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Skins Yükleme Paneli</h1>
        <form id="skinsForm">
            <div class="form-group">
                <label for="desenId">Desen ID</label>
                <input type="text" id="desenId" placeholder="Desen ID girin" required>
            </div>
            <div class="form-group">
                <label for="desenAdi">Desen Adı</label>
                <input type="text" id="desenAdi" placeholder="Desen Adı girin" required>
            </div>
            <div class="form-group">
                <label for="desenLink">Desen Görsel Linki</label>
                <input type="text" id="desenLink" placeholder="Desen Görsel Linki girin" required>
            </div>
            <div class="form-group">
                <label>Glow Renk Kodları</label>
                <div id="glowColors">
                    <!-- Glow renk kutucukları buraya eklenecek -->
                </div>
            </div>
            <button type="submit">PHP Dosyasına Gönder</button>
        </form>
    </div>

    <script>
        // Glow renk kutucuklarını oluştur
        const glowColorsContainer = document.getElementById('glowColors');
        const glowColors = [];
        for (let i = 0; i < 8; i++) {
            const colorInputDiv = document.createElement('div');
            colorInputDiv.innerHTML = `
                <input type="color" id="glowColor${i}" value="#ffffff">
                <input type="text" id="glowText${i}" placeholder="Glow Renk Kodu (Örn: a__red)">
            `;
            glowColorsContainer.appendChild(colorInputDiv);
            glowColors.push({ textInputId: `glowText${i}` });
        }

        // Form gönderme
        document.getElementById('skinsForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const desenId = document.getElementById('desenId').value;
            const desenAdi = document.getElementById('desenAdi').value;
            const desenLink = document.getElementById('desenLink').value;

            const glow = glowColors.map(({ textInputId }) => {
                const textInput = document.getElementById(textInputId).value;
                return textInput || 'a__default'; // Eğer boş bırakılırsa varsayılan renk
            });

            const data = {
                desenId,
                desenAdi,
                desenLink,
                glow
            };

            fetch('./process_skins.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.text())
            .then(result => {
                alert('Veriler başarıyla işlendi!');
                console.log(result);
            })
            .catch(error => {
                console.error('Hata oluştu:', error);
            });
        });
    </script>
</body>
</html>
