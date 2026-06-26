/**
 * Shared HR module JavaScript helpers
 */
(function (window) {
    function apiUrl() {
        return window.APP_URL || '/bti/';
    }

    window.HrModule = {
        apiUrl: apiUrl,
        parseJson: function (r) {
            return r.text().then(function (text) {
                try { return JSON.parse(text); }
                catch (e) { throw new Error(text || 'Invalid server response (HTTP ' + r.status + ')'); }
            });
        },
        success: function (message, thenFn) {
            Swal.fire({ icon: 'success', title: 'Success!', text: message, timer: 2000, showConfirmButton: false })
                .then(function () { if (thenFn) thenFn(); });
        },
        error: function (message) {
            Swal.fire({ icon: 'error', title: 'Error!', text: message || 'Something went wrong' });
        },
        get: function (endpoint) {
            return fetch(apiUrl() + endpoint).then(this.parseJson);
        },
        post: function (endpoint, data) {
            return fetch(apiUrl() + endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data || {})
            }).then(this.parseJson);
        },
        escapeHtml: function (str) {
            if (str == null) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        },
        formatDate: function (d) { return d ? String(d).substring(0, 10) : '—'; },
        badge: function (text, cls) {
            return '<span class="badge bg-' + (cls || 'secondary') + '">' + this.escapeHtml(String(text).replace(/_/g, ' ')) + '</span>';
        }
    };
})(window);
