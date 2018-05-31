<!DOCTYPE html>
<html>

    <head>
        <title>Untitled Document</title>
        <meta charset="UTF-8">

        <link rel="stylesheet" type="text/css" href="styles/no_javascript.css" />
    </head>

    <body>


        <div class="wrap">
            <div class="error">
                <?php
                session_start();
                session_destroy();
                ?>
                <h1>Are you using a browser that doesn't support JavaScript?</h1>
                <h2>If your browser does not support <strong>JavaScript</strong>, you can upgrade to a modern browser</h2>
                <ul class="browser_list">
                    <li><a href="https://www.firefox.com/" target="_blank"><img src="assets/icon_firefox.png" alt="Firefox"/></a></li>
                    <li><a href="https://www.google.com/chrome/" target="_blank"><img src="assets/icon_chrome.png" alt="Chrome"/></a></li>
                    <li><a href="https://www.apple.com/safari/" target="_blank"><img src="assets/icon_safari.png" alt="Safari"/></a></li>
                    <li><a href="http://www.opera.com/" target="_blank"><img src="assets/icon_opera.png" alt="Opera"/></a></li>
                    <li><a href="https://www.microsoft.com/en-us/windows/microsoft-edge" target="_blank"><img src="assets/icon_edge.png" alt="Microsoft Edge"/></a></li>
                    <li><a href="http://windows.microsoft.com/ie" target="_blank"><img src="assets/icon_explorer.png" alt="Internet Explorer"/></a></li>
                </ul>
                <div class="orr">- OR -</div>
                <h3>If you have disabled <strong>JavaScript</strong>, you must re-enable it to use opensis.</h3>
            </div>
        </div>

    </body>
</html>