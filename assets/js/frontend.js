/**
 * Frontend JavaScript for My Media Plugin
 */

(function($) {
    'use strict';

    const MMP = {
        init: function() {
            this.setupCameraCapture();
            this.setupScreenshot();
            this.setupLiveStream();
            this.setupMessaging();
            this.setupGallery();
            this.setupWhatsApp();
        },

        setupCameraCapture: function() {
            const self = this;
            
            $('#mmp-capture-btn').on('click', function() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: true })
                        .then(function(stream) {
                            const video = document.createElement('video');
                            video.srcObject = stream;
                            video.play();

                            // Capture after 1 second
                            setTimeout(function() {
                                const canvas = document.createElement('canvas');
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(video, 0, 0);

                                const imageData = canvas.toDataURL('image/png');
                                self.sendMedia(imageData, 'photo');

                                // Stop stream
                                stream.getTracks().forEach(track => track.stop());
                            }, 1000);
                        })
                        .catch(function(error) {
                            console.error('Camera access denied:', error);
                            alert('Please allow camera access');
                        });
                } else {
                    alert('Camera not supported in your browser');
                }
            });
        },

        setupScreenshot: function() {
            const self = this;
            
            $('#mmp-screenshot-btn').on('click', function() {
                if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
                    navigator.mediaDevices.getDisplayMedia({ video: true })
                        .then(function(stream) {
                            const video = document.createElement('video');
                            video.srcObject = stream;
                            video.play();

                            setTimeout(function() {
                                const canvas = document.createElement('canvas');
                                canvas.width = video.videoWidth;
                                canvas.height = video.videoHeight;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(video, 0, 0);

                                const imageData = canvas.toDataURL('image/png');
                                self.sendMedia(imageData, 'screenshot');

                                stream.getTracks().forEach(track => track.stop());
                            }, 500);
                        })
                        .catch(function(error) {
                            console.error('Screenshot access denied:', error);
                        });
                } else {
                    alert('Screenshot not supported in your browser');
                }
            });
        },

        setupLiveStream: function() {
            const self = this;
            
            $('#mmp-start-stream-btn').on('click', function() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: true, audio: true })
                        .then(function(stream) {
                            const video = document.getElementById('mmp-stream-preview');
                            if (video) {
                                video.srcObject = stream;
                                video.play();
                            }

                            // Send AJAX request to start stream
                            $.ajax({
                                url: mmpAjax.ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'start_stream',
                                    _ajax_nonce: mmpAjax.nonce,
                                    title: 'Live Stream ' + new Date().toLocaleString()
                                },
                                success: function(response) {
                                    console.log('Stream started:', response.data);
                                    $('#mmp-start-stream-btn').text('Stop Stream').removeClass('btn-success').addClass('btn-danger');
                                }
                            });
                        })
                        .catch(function(error) {
                            console.error('Camera/Microphone access denied:', error);
                            alert('Please allow camera and microphone access');
                        });
                }
            });
        },

        setupMessaging: function() {
            const self = this;
            
            // Load messages
            this.loadMessages();

            // Send message
            $(document).on('click', '#mmp-send-message-btn', function() {
                const message = $('#mmp-message-input').val();
                const recipientId = $('#mmp-recipient-select').val();

                if (!message.trim()) {
                    alert('Message cannot be empty');
                    return;
                }

                $.ajax({
                    url: mmpAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'send_message',
                        _ajax_nonce: mmpAjax.nonce,
                        message: message,
                        recipient_id: recipientId
                    },
                    success: function(response) {
                        $('#mmp-message-input').val('');
                        self.loadMessages();
                        console.log('Message sent successfully');
                    }
                });
            });
        },

        setupGallery: function() {
            this.loadGallery();
        },

        setupWhatsApp: function() {
            const self = this;

            $(document).on('click', '#mmp-whatsapp-send-btn', function() {
                const phone = $('#mmp-whatsapp-phone').val();
                const message = $('#mmp-whatsapp-message').val();

                if (!phone || !message) {
                    alert('Phone and message are required');
                    return;
                }

                $.ajax({
                    url: mmpAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'whatsapp_send',
                        _ajax_nonce: mmpAjax.nonce,
                        phone: phone,
                        message: message
                    },
                    success: function(response) {
                        alert('WhatsApp message sent successfully');
                        $('#mmp-whatsapp-phone').val('');
                        $('#mmp-whatsapp-message').val('');
                    },
                    error: function() {
                        alert('Failed to send WhatsApp message');
                    }
                });
            });
        },

        sendMedia: function(imageData, type) {
            $.ajax({
                url: mmpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'capture_media',
                    _ajax_nonce: mmpAjax.nonce,
                    type: type,
                    image: imageData
                },
                success: function(response) {
                    console.log('Media captured:', response.data);
                    alert('Media captured successfully!');
                },
                error: function() {
                    alert('Failed to capture media');
                }
            });
        },

        loadMessages: function() {
            $.ajax({
                url: mmpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_messages',
                    _ajax_nonce: mmpAjax.nonce
                },
                success: function(response) {
                    const messages = response.data;
                    const container = $('#mmp-messages-container');
                    
                    if (container.length) {
                        container.empty();
                        
                        messages.forEach(function(msg) {
                            const html = '<div class="mmp-message"><strong>' + msg.message_type + ':</strong> ' + msg.message_content + ' <em>' + msg.created_at + '</em></div>';
                            container.append(html);
                        });
                    }
                }
            });
        },

        loadGallery: function() {
            $.ajax({
                url: mmpAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_gallery',
                    _ajax_nonce: mmpAjax.nonce
                },
                success: function(response) {
                    const media = response.data;
                    const gallery = $('#mmp-gallery-grid');
                    
                    if (gallery.length) {
                        gallery.empty();
                        
                        media.forEach(function(item) {
                            const html = '<div class="mmp-gallery-item"><img src="' + item.file_url + '" alt="' + item.capture_type + '" /><p>' + item.capture_type + ' - ' + item.created_at + '</p></div>';
                            gallery.append(html);
                        });
                    }
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        MMP.init();
    });

})(jQuery);
