document.getElementById("addItemBtn").onclick = function () {
    let input = document.getElementById("newItemInput");
    if (!input.value.trim()) return;

    let li = document.createElement("li");
    li.textContent = input.value;
    li.onclick = () => li.remove(); 

    document.getElementById("itemsList").appendChild(li);
    input.value = "";
};
