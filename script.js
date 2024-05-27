// Função para adicionar um invocador
function addSummoner(event) {
    event.preventDefault();
    const gameName = document.getElementById('gameName').value;
    const tagLine = document.getElementById('tagLine').value;

    // Verifica se os campos estão preenchidos
    if (gameName === '' || tagLine === '') {
        alert('Por favor, preencha todos os campos.');
        return;
    }

    const formData = new FormData();
    formData.append('gameName', gameName);
    formData.append('tagLine', tagLine);

    // Mostrar o loading
    document.getElementById('loading').style.display = 'block';

    // Envia a requisição para o servidor
    fetch('add_summoner.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Esconder o loading
        document.getElementById('loading').style.display = 'none';
        document.getElementById('message').innerHTML = data;
        loadRankings();
    })
    .catch(error => {
        // Esconder o loading em caso de erro
        document.getElementById('loading').style.display = 'none';
        console.error('Erro:', error);
    });
}

// Função para carregar os rankings
function loadRankings() {
    // Mostrar o loading
    document.getElementById('loading').style.display = 'block';

    // Envia a requisição para o servidor
    fetch('display_rankings.php')
        .then(response => response.text())
        .then(data => {
            // Esconder o loading
            document.getElementById('loading').style.display = 'none';
            document.getElementById('rankings').innerHTML = data;
        })
        .catch(error => {
            // Esconder o loading em caso de erro
            document.getElementById('loading').style.display = 'none';
            console.error('Erro:', error);
        });
}

// Carregar rankings ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    loadRankings();
});

// Adicionar evento ao formulário
document.getElementById('summonerForm').addEventListener('submit', addSummoner);
