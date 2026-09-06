/**
 * Admin JavaScript for My Media Plugin
 */

(function($) {
    'use strict';

    const MMP_Admin = {
        init: function() {
            this.setupTabs();
            this.refreshData();
        },

        setupTabs: function() {
            $(document).on('click', '.mmp-tab', function() {
                const tabName = $(this).data('tab');
                $('.mmp-tab-content').hide();
                $('#' + tabName).show();
                $('.mmp-tab').removeClass('active');
                $(this).addClass('active');
            });
        },

        refreshData: function() {
            // Auto-refresh data every 30 seconds
            setInterval(function() {
                location.reload();
            }, 30000);
        }
    };

    $(document).ready(function() {
        MMP_Admin.init();
    });

})(jQuery);
