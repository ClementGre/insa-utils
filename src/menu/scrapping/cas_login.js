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

function getIntranetSessCookie(){
    return new Promise((resolve, reject) => {
        axios.get('https://intranet.insa-lyon.fr', {
            headers: {...defaultHeaders},
            maxRedirects: 0
        }).catch(err => { // Redirect 302
            resolve(err.response.headers['set-cookie'][0].split(';')[0]);
        })
    });
}

async function getLoginPageTokens(intranetSessCookie){
    const sessionReq = await axios.get('https://login.insa-lyon.fr/cas/login?service=https%3A%2F%2Fintranet.insa-lyon.fr%2Fnode', {
        headers: {
            'Cookie': intranetSessCookie,
            ...defaultHeaders
        },
        maxRedirects: 0
    });

    let jsSessionId = sessionReq.headers['set-cookie'][0].split(';')[0].split('=')[1];
    let executionToken = sessionReq.data.split('<input type="hidden" name="execution" value="')[1].split('" />')[0];
    let ltToken = sessionReq.data.split('<input type="hidden" name="lt" value="')[1].split('" />')[0];

    return {jsSessionId, executionToken, ltToken};
}

function intranetLogin(intranetSessCookie, jsSessionId, executionToken, ltToken){
    return new Promise((resolve, reject) => {
        axios.post('https://login.insa-lyon.fr/cas/login?service=https%3A%2F%2Fintranet.insa-lyon.fr%2Fnode', {
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
                'Cookie': "JSESSIONID=" + jsSessionId + ';' + intranetSessCookie,
                ...defaultHeaders
            },
            maxRedirects: 0
        }).catch(err => { // Redirect 302

            let url = err.response.headers['location'];
            let AGIMUS = err.response.headers['set-cookie'][0].split(';')[0].split('=')[1];
            let CASPRIVACY = err.response.headers['set-cookie'][1].split(';')[0].split('=')[1];
            let TGC = err.response.headers['set-cookie'][2].split(';')[0].split('=')[1];

            console.log('url:', url);
            console.log('AGIMUS:', AGIMUS);
            console.log('CASPRIVACY:', CASPRIVACY);
            console.log('TGC:', TGC);

            resolve({url, agimus: AGIMUS, casPrivacy: CASPRIVACY, tgc: TGC});
        })
    });
}
function authAtIntranet(url, intranetSessCookie, agimus){
    return new Promise((resolve, reject) => {
        axios.get(url, {
            headers: {
                'Cookie': intranetSessCookie + "; AGIMUS=" + agimus,
                ...defaultHeaders
            },
            maxRedirects: 0
        }).catch(err => { // Redirect 302

            axios.get("https://intranet.insa-lyon.fr/node", {
                headers: {
                    'Cookie': intranetSessCookie + "; AGIMUS=" + agimus,
                    ...defaultHeaders
                },
                maxRedirects: 0
            }).catch(err => { // Redirect 302
                resolve(err.response.headers['set-cookie'][0].split(';')[0]);
            });

        });
    })
}

async function connect(){

    let intranetSessCookie = await getIntranetSessCookie()
    let {jsSessionId, executionToken, ltToken} = await getLoginPageTokens(intranetSessCookie);
    let {url, agimus, casPrivacy, tgc} = await intranetLogin(intranetSessCookie, jsSessionId, executionToken, ltToken);
    let intranetSessCookie2 = await authAtIntranet(url, intranetSessCookie, agimus);

    axios.get('https://intranet.insa-lyon.fr/outils-de-communication', {
        headers: {
            'Cookie': intranetSessCookie2 + "; AGIMUS=" + agimus,
            ...defaultHeaders
        }
    }).then(res => {
        console.log('Fetched webpage:');
        console.log(res.data);
    });



}