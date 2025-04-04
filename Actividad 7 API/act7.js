class Brain {
    constructor() {
        this.images = [];
    }

    init = function init() {
        document.body.style.margin = "50px";
        document.body.style.backgroundColor = "#eeeeee";

        this.apiRandomPerson();
    }

    apiRandomPerson = function apiRandomPerson() {
        Util.createTitle("Persona Random:");
        let content = document.createElement("div");
        let url = "https://randomuser.me/api/?results=1";
        let image = document.createElement('img');
        let name = document.createElement('p');
        let email = document.createElement('p');
        let gender = document.createElement('p');
        let country = document.createElement('p');
        let phone = document.createElement('p');
        let loading = Util.getLoadingImg(); 
        document.body.appendChild(loading); 

        fetch(url)
            .then(response => response.json())
            .then(data => {
                let person = data.results[0];
                image.setAttribute("src", person.picture.large);
                name.textContent = "Nombre: " + person.name.first + " " + person.name.last;
                email.textContent = "Email: " + person.email;
                gender.textContent = "Género: " + person.gender;
                country.textContent = "País: " + person.location.country;
                phone.textContent = "Teléfono: " + person.phone;

                content.appendChild(image);
                content.appendChild(name);
                content.appendChild(email);
                content.appendChild(gender);
                content.appendChild(country);
                content.appendChild(phone);

                document.body.removeChild(loading); 
                document.body.appendChild(content); 
            })
            .catch(error => {
                console.error("Error cargando los datos: ", error);
                document.body.removeChild(loading);
            });
    }
}

class Util {
    static createTitle(name) {
        var title = document.createElement("h1");
        var text = document.createTextNode(name);
        title.id = "title";
        title.appendChild(text);
        title.style.marginTop = "50px";
        document.body.appendChild(title);
    }

    static getRandom(min, max) {
        return (Math.random() * (max - min)) + min;
    }

    static getLoadingImg() {
        let loading = document.createElement('img');
        loading.textContent = "Cargando...";
        loading.src = 'img/loading.gif'; 
        loading.style.height = "64px";
        loading.style.width = "64px";
        return loading;
    }
}

let brain = new Brain();
brain.init();
