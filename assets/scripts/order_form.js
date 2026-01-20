var costumerInput

window.addEventListener("load", async (event) => {
    costumerInput = document.getElementById('order_dto_Costumer')
    document.addEventListener('keydown', (event) => {
        costumerInput.focus()
    });
    document.addEventListener('click', (event) => {
        costumerInput.focus()
    })
})

