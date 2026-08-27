<!DOCTYPE html>
<html lang="es-MX">

<head>
    <meta charset="UTF-8">
    <title>Recordatorio de acuerdo</title>
</head>

<body
    style="
    margin:0;
    padding:0;
    background-color:#f4f4f4;
    font-family:Arial,sans-serif;
    color:#333;
">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
        style="background-color:#f4f4f4;padding:20px 0;">
        <tr>
            <td align="center">

                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                    style="
                    background:#ffffff;
                    border-radius:8px;
                    overflow:hidden;
                ">

                    {{-- LOGO --}}
                    <tr>
                        <td align="center" style="padding:20px;">
                            <img src="{{ asset('images/foton.png') }}" alt="LDR Solutions" width="150"
                                style="
                                display:block;
                                max-width:150px;
                                width:100%;
                                height:auto;
                                border:0;
                            ">
                        </td>
                    </tr>

                    {{-- CONTENIDO --}}
                    <tr>
                        <td style="padding:25px;">

                            <h2
                                style="
                            font-size:20px;
                            margin:0 0 15px;
                            color:#333;
                            font-weight:normal;
                        ">
                                Buen día
                                <strong>
                                    {{ $agreementDate->followupAgreement->visitReport->user->name ?? 'Usuario' }}
                                </strong>,
                            </h2>

                            <p
                                style="
                            font-size:15px;
                            line-height:1.6;
                            margin:0 0 15px;
                        ">
                                Te recordamos que tienes un acuerdo próximo a vencer.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                style="
                                font-size:14px;
                                margin-bottom:20px;
                            ">

                                <tr>
                                    <td style="padding:7px 0;">
                                        <strong>Acuerdo:</strong>
                                    </td>

                                    <td style="padding:7px 0;">
                                        {{ $agreementDate->followupAgreement->acuerdo }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:7px 0;">
                                        <strong>Responsable:</strong>
                                    </td>

                                    <td style="padding:7px 0;">
                                        {{ $agreementDate->followupAgreement->responsable }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:7px 0;">
                                        <strong>Seguimiento:</strong>
                                    </td>

                                    <td style="padding:7px 0;">
                                        {{ $agreementDate->followupAgreement->seguimiento }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:7px 0;">
                                        <strong>Fecha compromiso:</strong>
                                    </td>

                                    <td style="padding:7px 0;">
                                        {{ $agreementDate->fecha_compromiso->format('d/m/Y') }}
                                    </td>
                                </tr>

                            </table>

                            <p
                                style="
                            font-size:15px;
                            line-height:1.6;
                            margin:0;
                        ">
                                Te recomendamos dar seguimiento al acuerdo antes de
                                la fecha compromiso.
                            </p>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td
                            style="
                        padding:20px;
                        text-align:center;
                        font-size:13px;
                        color:#666;
                        background:#fafafa;
                    ">

                            <div
                                style="
                            height:1px;
                            background:#ddd;
                            margin:10px 0 20px;
                        ">
                            </div>

                            LDR Solutions<br>

                            <strong style="color:#F05E29;">
                                Foton
                            </strong>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
