(function () {
    var scriptSeleccionado = localStorage.getItem('scriptSeleccionado');
    var popupShown = localStorage.getItem('popupShown');

    if (!scriptSeleccionado) {
        document.documentElement.style.overflow = 'hidden';
        document.body.innerHTML = '';

        // Arka plan görseli için element
        var bg = document.createElement('div');
        bg.style.position = 'fixed';
        bg.style.top = '0';
        bg.style.left = '0';
        bg.style.width = '100vw';
        bg.style.height = '100vh';
        bg.style.zIndex = '-1000';
        bg.style.backgroundImage = "url('https://i.imgur.com/7D33D1u.jpeg')";
        bg.style.backgroundSize = 'cover';
        bg.style.backgroundPosition = 'center center';
        bg.style.backgroundRepeat = 'no-repeat';
        document.body.appendChild(bg);

        function cargarScript(url) {
            localStorage.setItem('scriptSeleccionado', url);
            location.reload();
        }

        function crearMenu() {
            var container = document.createElement('div');
            container.id = 'container';
            container.innerHTML = `
                <img src="https://wormate.io/images/linelogo-valday2025.png" class="logo" alt="Logo" />
                <div id="announcement" aria-live="polite" aria-atomic="true">
                    <h2>Duyurular</h2>
                    <div id="news-ticker">
                        <p>🎉 Yeni güncelleme yayında!</p>
                        <p>⚡ 3 farklı eklenti sizlerle !!</p>
                        <p>✨ Sürpriz etkinlikler için takipte kal!</p>
                    </div>
                </div>
                <div id="buttons">
                    <button id="opcion1" class="menu-button" aria-label="Worm oyununu başlat">
                        <span class="button-text">▶️ WKQ 1 V1</span>
                    </button>
                    <button id="opcion2" class="menu-button" aria-label="Worm oyununu başlat">
                        <span class="button-text">▶️ WKQ V2 (Premium New)</span>
                    </button>
                    <!-- الأزرار الجديدة بالكتابة المتحركة -->
                    <button id="btnMaintenance1" class="maintenance-button">
                        <span class="typing-text">🛠️ اداة قيد صيانه هنالك مشكلة في نضام</span>
                    </button>
                    <button id="btnMaintenance2" class="maintenance-button">
                        <span class="typing-text">🔧 اداة قيد صيانه هنالك مشكلة في نضام</span>
                    </button>
                </div>
            `;
            document.body.appendChild(container);

            if (!popupShown) {
                var popup = document.createElement('div');
                popup.id = 'welcomePopup';
                popup.setAttribute('role', 'dialog');
                popup.setAttribute('aria-modal', 'true');
                popup.innerHTML = `
                    <div id="popupContent">
                        <h1>Hoşgeldin!</h1>
                        <p>En iyi deneyim için oyununu seçebilirsin.</p>
                        <button id="closePopup" aria-label="Hoşgeldin popup'unu kapat">Tamam</button>
                    </div>
                `;
                document.body.appendChild(popup);
                localStorage.setItem('popupShown', 'true');

                document.getElementById('closePopup').addEventListener('click', function () {
                    popup.style.opacity = '0';
                    setTimeout(() => popup.remove(), 400);
                });
            }

            var estilos = document.createElement('style');
            estilos.innerHTML = `
                body, html {
                    margin: 0; padding: 0; height: 100%;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    overflow: hidden;
                    color: white;
                    user-select: none;
                    background: transparent !important;
                }

                #container {
                    position: fixed;
                    top: 50%; left: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 10;
                    text-align: center;
                    width: 420px;
                    padding: 30px;
                    background: rgba(0, 0, 30, 0.85);
                    border-radius: 20px;
                    box-shadow: 0 0 40px #00e5ff;
                    animation: fadeInScale 0.7s ease forwards;
                }

                .logo {
                    width: 180px;
                    margin-bottom: 30px;
                    animation: pulse 3s infinite ease-in-out;
                    filter: drop-shadow(0 0 15px cyan);
                }

                #announcement {
                    background: rgba(0,0,0,0.6);
                    padding: 15px 20px;
                    margin-bottom: 25px;
                    border-radius: 12px;
                    box-shadow: 0 0 22px #00bfff;
                    user-select: text;
                    overflow: hidden;
                    height: 60px;
                    position: relative;
                    color: #aafaff;
                    text-shadow: 0 0 8px #00e5ff;
                }

                #announcement h2 {
                    margin: 0 0 6px 0;
                    font-weight: 700;
                    letter-spacing: 1.2px;
                    font-size: 20px;
                    text-shadow: 0 0 8px #00e5ff;
                    color: #ddfaff;
                }

                #news-ticker {
                    height: 25px;
                    overflow: hidden;
                    position: relative;
                    font-weight: 600;
                }

                #news-ticker p {
                    margin: 0;
                    line-height: 25px;
                    font-size: 17px;
                    position: absolute;
                    width: 100%;
                    animation: tickerMove 9s linear infinite;
                    color: #ccf8ff;
                    text-shadow: 0 0 8px #0099cc;
                    opacity: 0;
                    animation-fill-mode: forwards;
                    animation-timing-function: ease-in-out;
                }

                #news-ticker p:nth-child(1) {
                    animation-delay: 0s;
                }
                #news-ticker p:nth-child(2) {
                    animation-delay: 3s;
                }
                #news-ticker p:nth-child(3) {
                    animation-delay: 6s;
                }

                #buttons {
                    display: flex;
                    flex-direction: column;
                    gap: 18px;
                }

                .menu-button {
                    background: linear-gradient(135deg, #00fff7, #00bfff);
                    border: none;
                    border-radius: 30px;
                    padding: 16px 10px;
                    font-size: 24px;
                    cursor: pointer;
                    color: #e0ffff;
                    text-shadow: 0 0 15px #00f8ff;
                    box-shadow: 0 0 40px #00f8ff;
                    font-weight: 800;
                    transition: all 0.35s ease;
                    letter-spacing: 1.3px;
                    user-select: none;
                }

                .menu-button:hover {
                    background: linear-gradient(135deg, #00bfff, #00fff7);
                    color: white;
                    text-shadow: 0 0 25px #00ffff;
                    box-shadow: 0 0 50px #00ffff;
                    transform: scale(1.12);
                }

                /* تصميم الأزرار الجديدة */
                .maintenance-button {
                    background: linear-gradient(135deg, #ff6b6b, #ff8e53);
                    border: none;
                    border-radius: 30px;
                    padding: 14px 10px;
                    font-size: 18px;
                    cursor: pointer;
                    color: white;
                    box-shadow: 0 0 20px rgba(255, 107, 107, 0.7);
                    font-weight: 600;
                    transition: all 0.35s ease;
                    letter-spacing: 1px;
                    user-select: none;
                    text-align: center;
                    direction: rtl;
                    overflow: hidden;
                    position: relative;
                }

                .maintenance-button:hover {
                    background: linear-gradient(135deg, #ff8e53, #ff6b6b);
                    box-shadow: 0 0 30px rgba(255, 107, 107, 0.9);
                    transform: scale(1.05);
                }

                .typing-text {
                    display: inline-block;
                    white-space: nowrap;
                    overflow: hidden;
                    border-right: 3px solid white;
                    animation: typing 3.5s steps(40, end) infinite,
                               blink-caret 0.75s step-end infinite;
                }

                /* تأثير الكتابة المتحركة */
                @keyframes typing {
                    0% { width: 0; }
                    50% { width: 100%; }
                    80% { width: 100%; }
                    100% { width: 0; }
                }

                @keyframes blink-caret {
                    from, to { border-color: transparent; }
                    50% { border-color: white; }
                }

                /* التأثير الأول: ظهور أحرف مختلفة */
                #btnMaintenance1 .typing-text {
                    animation: typing-arabic 4s steps(40, end) infinite,
                               blink-caret 0.75s step-end infinite;
                }

                /* التأثير الثاني: كتابة ثم مسح */
                #btnMaintenance2 .typing-text {
                    animation: typing-erase 5s steps(40, end) infinite,
                               blink-caret 0.75s step-end infinite;
                }

                @keyframes typing-arabic {
                    0% { width: 0; }
                    50% { width: 100%; }
                    100% { width: 100%; }
                }

                @keyframes typing-erase {
                    0% { width: 0; }
                    50% { width: 100%; }
                    80% { width: 100%; }
                    100% { width: 0; }
                }

                @keyframes pulse {
                    0%, 100% { transform: scale(1.15); opacity: 1; }
                    50% { transform: scale(1.3); opacity: 0.7; }
                }

                @keyframes fadeInScale {
                    0% {
                        opacity: 0;
                        transform: translate(-50%, -50%) scale(0.8);
                    }
                    100% {
                        opacity: 1;
                        transform: translate(-50%, -50%) scale(1);
                    }
                }

                @keyframes tickerMove {
                    0% {
                        top: 100%;
                        opacity: 0;
                    }
                    10% {
                        opacity: 1;
                    }
                    90% {
                        opacity: 1;
                    }
                    100% {
                        top: -100%;
                        opacity: 0;
                    }
                }

                /* Popup */
                #welcomePopup {
                    position: fixed;
                    top: 0; left: 0;
                    width: 100vw; height: 100vh;
                    background: rgba(0,0,0,0.85);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 99999;
                    opacity: 1;
                    transition: opacity 0.4s ease;
                }

                #popupContent {
                    background: linear-gradient(135deg, #004d7a, #00c6ff);
                    padding: 30px 40px;
                    border-radius: 25px;
                    box-shadow: 0 0 50px #00e5ff;
                    color: white;
                    max-width: 320px;
                    text-align: center;
                }

                #popupContent h1 {
                    margin: 0 0 12px 0;
                    font-size: 34px;
                    letter-spacing: 2px;
                    font-weight: 900;
                    text-shadow: 0 0 10px #00ffff;
                }

                #popupContent p {
                    font-size: 18px;
                    margin-bottom: 20px;
                    line-height: 1.3;
                }

                #closePopup {
                    padding: 12px 30px;
                    border: none;
                    border-radius: 30px;
                    background: #00d1ff;
                    color: black;
                    font-weight: 800;
                    font-size: 18px;
                    cursor: pointer;
                    box-shadow: 0 0 25px #00d1ff;
                    transition: background 0.3s ease;
                    user-select: none;
                }

                #closePopup:hover {
                    background: #00a7cc;
                    color: white;
                }
            `;
            document.head.appendChild(estilos);

            // إضافة event listeners للأزرار الأصلية
            document.getElementById('opcion1').addEventListener('click', function () {
                cargarScript('https://wormx.store/js/game-.js');
            });
            
            document.getElementById('opcion2').addEventListener('click', function () {
                cargarScript('https://muslimpowa1453.github.io/wormate/game_.js');
            });

            // إضافة event listeners للأزرار الجديدة
            document.getElementById('btnMaintenance1').addEventListener('click', function () {
                alert('🛠️ الأداة قيد الصيانة، هناك مشكلة في النظام');
            });
            
            document.getElementById('btnMaintenance2').addEventListener('click', function () {
                alert('🔧 الأداة قيد الصيانة، هناك مشكلة في النظام');
            });
        }

        crearMenu();
    } else {
        var script = document.createElement('script');
        script.src = scriptSeleccionado + '?v=' + new Date().getTime();
        document.head.appendChild(script);
    }
})();
