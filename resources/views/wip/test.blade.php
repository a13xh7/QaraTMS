<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link
        rel="stylesheet"
        href="//cdnjs.cloudflare.com/ajax/libs/jodit/3.18.9/jodit.min.css"
    />
    <script src="//cdnjs.cloudflare.com/ajax/libs/jodit/3.18.9/jodit.min.js"></script>

</head>
<body>

<textarea id="editor"></textarea>

<hr>

<textarea></textarea>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>

<script>
    const editor2 = Jodit.make("#editor", {
        "showCharsCounter": false,
        "showWordsCounter": false,
        "showXPathInStatusbar": false,
        "buttons": "bold,italic,underline,strikethrough,ul,ol,table,link,align",

    });


    $('textarea').each(function () {
        var editor = Jodit.make(this, {
            uploader: {
                url: 'http://localhost:8181/index.php?action=fileUpload'
            },
            filebrowser: {
                ajax: {
                    url: 'http://localhost:8181/index.php'
                }
            }
            }
        );
        editor.value = '<p>start</p>';
    });

</script>

</body>
</html>
