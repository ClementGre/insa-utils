const axios = require("axios");
const cron = require("cron");
const editJsonFile = require("edit-json-file");

const defaultHeaders = {
    'Accept-Language': 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
    'Accept-Encoding': 'gzip, deflate, br',
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:108.0) Gecko/20100101 Firefox/108.0',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    'Content-Type': 'application/x-www-form-urlencoded'
}

connect().then(_ => {});

async function connect(){
    axios.get('https://menu-restaurants.insa-lyon.fr/API/public/v1/Semaine/4/1/32/2023-01-16', {
        headers: {
            'Authorization': 'Basic c2FsYW1hbmRyZTpzYWxhbQ==',
            ...defaultHeaders
        }
    }).then(res => {
        let cache = editJsonFile("./cache.json")
        cache.set("data", res.data);
        cache.save();
    });



}