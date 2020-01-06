{!!
    QrCode::format('png')->size($size)->margin($margin)->generate($data /*Request::url()*/, '../public/files/qr/qrcode.png')
 !!}