$(document).ready(function() {
    if ($('#content').length) {
        if (typeof $.fn.summernote === 'undefined') {
            console.error('Summernote is not loaded properly');
            return;
        }

        try {
            $('#content').summernote({
                height: 400,
                placeholder: 'Describe the content of your document here...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'italic', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
        } catch (e) {
            console.error('Error initializing Summernote:', e);
        }

        $('#documentForm').on('submit', function() {
            const btn = $('#submit_btn');
            btn.prop('disabled', true)
               .html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');
        });
    }
}); 