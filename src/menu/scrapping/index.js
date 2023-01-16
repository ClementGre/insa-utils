const axios = require("axios");
const cron = require("cron");
const editJsonFile = require("edit-json-file");
const secrets = require('./secrets.json');


connect().then(_ => {});

async function connect(){
    const sessionReq = await axios('https://login.insa-lyon.fr/cas/login')

    let JSSESSIONID = sessionReq.headers['set-cookie'][0].split(';')[0].split('=')[1];
    let executionToken = sessionReq.data.split('<input type="hidden" name="execution" value="')[1].split('" />')[0];
    let ltToken = sessionReq.data.split('<input type="hidden" name="lt" value="')[1].split('" />')[0];

    console.log("JSSESSIONID: ", JSSESSIONID)
    console.log("ltToken: ", ltToken);
    console.log("executionToken: ", executionToken)

    console.log(sessionReq);

    const loginReq = await axios('https://login.insa-lyon.fr/cas/login', {
        method: 'post',
        headers: {
            'Cookie': "JSESSIONID=" + JSSESSIONID
        },
        data: {
            "username": secrets.cas_login,
            "password": secrets.cas_password,
            "lt": ltToken,
            "execution": executionToken,
            "_eventId": "submit",
            "submit": "SE+CONNECTER"
        },
    })

    console.log(loginReq);
}