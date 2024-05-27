<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cadastro de Invocadores</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link para o arquivo CSS externo -->
    <script src="script.js" defer></script> <!-- Link para o arquivo JavaScript externo -->
</head>

<body>
    <h1>Cadastro de Invocadores</h1>
    <form id="summonerForm">
        <div class="form-control">
            <input type="text" id="gameName" name="gameName" required>
            <label>
                <span style="transition-delay:0ms">N</span><span style="transition-delay:50ms">o</span><span
                    style="transition-delay:100ms">m</span><span style="transition-delay:150ms">e</span>
                <span style="transition-delay:200ms">d</span><span style="transition-delay:250ms">o</span>
                <span style="transition-delay:300ms">I</span><span style="transition-delay:350ms">n</span><span
                    style="transition-delay:400ms">v</span><span style="transition-delay:450ms">o</span><span
                    style="transition-delay:500ms">c</span><span style="transition-delay:550ms">a</span><span
                    style="transition-delay:600ms">d</span><span style="transition-delay:650ms">o</span><span
                    style="transition-delay:700ms">r</span>
            </label>
        </div>
        <div class="form-control">
            <input type="text" id="tagLine" name="tagLine" required>
            <label>
                <span style="transition-delay:0ms">T</span><span style="transition-delay:50ms">a</span><span
                    style="transition-delay:100ms">g</span>
            </label>
        </div>
        <button type="submit">
            <span>ADICIONAR</span>
        </button>
    </form>
    <h2>Rankings</h2>
    <div id="message"></div>
    <div id="loading" style="display: none;">Carregando aguarde...</div>
    <div id="rankings"></div>
</body>

</html>
