<div class="popup">
    <button class="cross" onclick="retirerPopUp()">X</button>
    <h1><?php echo htmlspecialchars($titre); ?></h1>
    <p class="explication"><?php var_dump($btn1['arguments']); ?></p>
    <div class="btns">
        <button id="btn1"><?php echo htmlspecialchars($btn1['libelle']); ?></button>
        <button id="btn2"><?php echo htmlspecialchars($btn2['libelle']); ?></button>
    </div>
</div>

<div class="voile-popup"></div>

<script>
    document.addEventListener("DOMContentLoaded", function(){
        const btn1Data = <?php echo json_encode($btn1); ?>;
        const btn2Data = <?php echo json_encode($btn2); ?>;

        document.getElementById('btn1').addEventListener('click', function() {
            console.log('ok');
            let bodyString = btn1Data.arguments.map(arg => `${encodeURIComponent(arg[0])}=${encodeURIComponent(arg[1])}`).join('&');

            fetch('../../fonctions/controleur.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: bodyString
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                retirerPopUp();
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
        });

        document.getElementById('btn2').addEventListener('click', function() {
            let bodyString = btn1Data.arguments.map(arg => `${encodeURIComponent(arg[0])}=${encodeURIComponent(arg[1])}`).join('&');

            fetch('../../fonctions/controleur.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: bodyString
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                retirerPopUp();
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
        });

        function retirerPopUp() {
            // Implement the logic to remove the popup
            document.querySelector('.popup').style.display = 'none';
            document.querySelector('.voile-popup').style.display = 'none';
        }
    }) 
</script>