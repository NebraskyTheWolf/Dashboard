const express = require('express')
const bwipJs= require('bwip-js');
const nodeBase64 = require('nodejs-base64-converter');
const path = require('path')
const fs = require("fs"),
    { createCanvas, loadImage, PNGStream} = require("canvas");

const http = require('http')
const bodyParser = require("body-parser");

const app = express()

app.use(express.static(path.join(__dirname, '../public')))
app.use(bodyParser())

app.post('/order', async function (req, res) {
    bwipJs.toBuffer({
        bcid: 'datamatrix',
        text: nodeBase64.encode(req.body.orderId),
        barcolor: '#000',

    }).then(png => {
        const dout = fs.createWriteStream('/workspace/storage/app/public/' + req.body.orderId + '-order.png'),
            dstream = new PNGStream.from(png);
        dstream.pipe(dout);

        return res.status(200).json({
            'status': true,
            'result': 'success'
        })
    })
})

app.post('/voucher', async function (req, res) {
    const price = req.body.price
    const decoded = JSON.parse(nodeBase64.decode(req.body.properties))
    const id = nodeBase64.decode(decoded.data);

    console.log(id)

    if (decoded.signature === undefined || decoded.data === undefined) {
        return res.status(404).json({
            'status': false,
            'error': 'VOUCHER_ID_MISSING',
            'message': 'The voucher id is missing.'
        })
    }

    const canvas = createCanvas(806, 988),
        ctx = canvas.getContext("2d");

    await loadImage(path.join(__dirname, 'public', 'default_voucher_cards.png')).then(async img => {
        ctx.drawImage(img, 0, 0);

        ctx.font = '26px "Arial Bold"';
        ctx.fillStyle = "rgb(255,255,255)";
        ctx.fillText(id, 130,470);
        ctx.fillText(price + ' Kc', 368,580);

        bwipJs.toBuffer({
            bcid: 'datamatrix',
            text: req.body.properties,
            barcolor: '#FFF'
        }).then(png => {
            const dout = fs.createWriteStream(path.join(__dirname, 'cache', id + '-datamatrix.png')),
                dstream = new PNGStream.from(png);
            dstream.pipe(dout);
        })

        setTimeout(async () => {
            await loadImage(path.join(__dirname, 'cache', id + '-datamatrix.png')).then(img => {
                ctx.drawImage(img, 536,728, 247, 247)
            })

            const out = fs.createWriteStream('/workspace/storage/app/public/' + id + '-code.png'),
                stream = canvas.createPNGStream();
            stream.pipe(out);
        }, 3000)

    })

    console.log(__dirname)

    res.status(200).json({
        'status': true,
        'message': 'The was was created.',
        'path': '/workspace/storage/app/public' + id + '-code.png'
    })
});

http.createServer(app).listen(3900)

