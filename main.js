document.addEventListener("DOMContentLoaded", function () {
    fetch("mantas_get.php")
        .then(response => response.json())
        .then(data => {
            let container = document.querySelector(".atrastasL");
            container.innerHTML = "";
            data.forEach(manta => {
                container.innerHTML += `
                    <div class="item">
                        <img src="${manta.attels}" alt="${manta.nosaukums}">
                        <h3>${manta.nosaukums}</h3>
                        <p>${manta.apraksts}</p>
                    </div>
                `;
            });
        });
});