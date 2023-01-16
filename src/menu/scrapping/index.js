const axios = require("axios");
const cron = require("cron");
const editJsonFile = require("edit-json-file");
const secrets = require('./secrets.json');

const defaultHeaders = {
    'Accept-Language': 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7',
    'Accept-Encoding': 'gzip, deflate, br',
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:108.0) Gecko/20100101 Firefox/108.0',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
    'Content-Type': 'application/x-www-form-urlencoded'
}

connect().then(_ => {});

function getIntranetSessId(){
    return new Promise((resolve, reject) => {
        axios.get('https://intranet.insa-lyon.fr', {
            headers: {...defaultHeaders},
            maxRedirects: 0
        }).catch(err => {
            resolve(err.response.headers['set-cookie'][0].split(';')[0]);
        })
    });
}

async function connect(){

    let intranetSessId = await getIntranetSessId()

    const sessionReq = await axios.get('https://login.insa-lyon.fr/cas/login', {
        headers: {
            'Cookie': intranetSessId,
            ...defaultHeaders
        },
        maxRedirects: 0
    });

    let JSSESSIONID = sessionReq.headers['set-cookie'][0].split(';')[0].split('=')[1];
    let executionToken = sessionReq.data.split('<input type="hidden" name="execution" value="')[1].split('" />')[0];
    let ltToken = sessionReq.data.split('<input type="hidden" name="lt" value="')[1].split('" />')[0];

    console.log('JSSESSIONID:', JSSESSIONID);

    const loginReq = await axios.post('https://login.insa-lyon.fr/cas/login', {
        "username": secrets.cas_login,
        "password": secrets.cas_password,
        "lt": ltToken,
        "execution": executionToken,
        "_eventId": "submit",
        "submit": "SE CONNECTER"
    }, {
        headers: {
            'Origin': 'https://login.insa-lyon.fr',
            'Referer': 'https://login.insa-lyon.fr/cas/login?service=https%3A%2F%2Fintranet.insa-lyon.fr%2Fnode',
            'Cookie': "JSESSIONID=" + JSSESSIONID + ';' + intranetSessId,
            ...defaultHeaders
        },
        maxRedirects: 0
    })
    console.log(loginReq);

    let AGIMUS = loginReq.headers['set-cookie'][0].split(';')[0].split('=')[1];
//    let CASPRIVACY = loginReq.headers['set-cookie'][1].split(';')[0].split('=')[1];
  //  let TGC = loginReq.headers['set-cookie'][2].split(';')[0].split('=')[1];

    console.log('AGIMUS:', AGIMUS);
    //console.log('CASPRIVACY:', CASPRIVACY);
    //console.log('TGC:', TGC);

    /*const intranetFinalReq = await axios.get('https://intranet.insa-lyon.fr', {
        headers: {
            'Cookie': "JSSESSIONID=" + JSSESSIONID0 + "; AGIMUS=" + AGIMUS, //+ "; CASPRIVACY=" + CASPRIVACY + "; TGC=" + TGC,
            ...defaultConfig
        },
        maxRedirects: 0
    });*/

    //console.log(intranetFinalReq);
}