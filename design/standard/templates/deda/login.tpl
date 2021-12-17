<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    {ezscript_load( array( 'ezjsc::jquery', 'spid-sp-access-button.min.js' ) )}
    {ezcss_load( array( 'spid-sp-access-button.min.css' ) )}
    <link rel="Shortcut icon" href="{"favicon.ico"|ezimage(no)}" type="image/x-icon"/>
    {literal}
        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
            }

            .box {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
                height: 30%;
            }

            .box div {
                width: 280px;
                margin: 0 20px;
                height: 100px;
                text-align: left;
            }

            .box p {
                font-family: "Titillium Web", HelveticaNeue-Light, "Helvetica Neue Light", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
                font-size: 1rem;
            }
        </style>
    {/literal}
</head>
<body>

<div class="box">
    <div>
        <p style="text-align: center">
            <img src="{'spid-agid-logo-lb.png'|ezimage(no)}" alt="AGID-SPID"
                 style="max-width: 280px;display: inline-block;"/>
        </p>
        <p>SPID è il sistema di identità digitale, valido a livello nazionale, che consente di accedere ai servizi
            online della Pubblica Amministrazione e dei privati accreditati.</p>
        <p>Se sei già in possesso di un'identità digitale SPID, seleziona il gestore della tua identità dal menù a
            tendina.</p>
        <p>Se non hai ancora un'identità digitale, richiedila ad uno dei gestori.</p>
        <p style="text-align: center">
            <a href="#" class="italia-it-button italia-it-button-size-l button-spid"
               spid-idp-button="#spid-idp-button-large-get" aria-haspopup="true" aria-expanded="false">
                    <span class="italia-it-button-icon">
                        <img src="{'spid-ico-circle-bb.svg'|ezimage(no)}"
                             onerror="this.src='{'spid-ico-circle-bb.png'|ezimage(no)}'; this.onerror=null;"
                             alt=""/>
                    </span>
                <span class="italia-it-button-text">Entra con SPID</span>
            </a>
        </p>
    </div>
</div>

<div id="spid-idp-button-large-get" class="spid-idp-button spid-idp-button-tip spid-idp-button-relative">
    <ul id="spid-idp-list-large-root-get" class="spid-idp-button-menu" aria-labelledby="spid-idp">
        <li class="spid-idp-button-link" data-idp="arubaid">
            <a href="{concat('deda/login/', 'arubaid')|ezurl(no)}">
                <span class="spid-sr-only">Aruba ID</span>
                <img src="{'spid-idp-arubaid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-arubaid.png'|ezimage(no)}'; this.onerror=null;"
                     alt="Aruba ID"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="infocertid">
            <a href="{concat('deda/login/', 'infocert')|ezurl(no)}">
                <span class="spid-sr-only">Infocert ID</span>
                <img src="{'spid-idp-infocertid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-infocertid.png'|ezimage(no)}'; this.onerror=null;"
                     alt="Infocert ID"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="intesaid">
            <a href="{concat('deda/login/', 'intesa')|ezurl(no)}">
                <span class="spid-sr-only">Intesa ID</span>
                <img src="{'spid-idp-intesaid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-intesaid.png'|ezimage(no)}'; this.onerror=null;"
                     alt="Intesa ID"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="lepidaid">
            <a href="{concat('deda/login/', 'lepida')|ezurl(no)}">
                <span class="spid-sr-only">Lepida ID</span>
                <img src="{'spid-idp-lepidaid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-lepidaid.png'|ezimage(no)}|ezimage(no)}'; this.onerror=null;"
                     alt="Lepida ID"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="namirialid">
            <a href="{concat('deda/login/', 'namirialid')|ezurl(no)}">
                <span class="spid-sr-only">Namirial ID</span>
                <img src="{'spid-idp-namirialid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-namirialid.png'|ezimage(no)}'; this.onerror=null;"
                     alt="Namirial ID"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="posteid">
            <a href="{concat('deda/login/', 'poste')|ezurl(no)}">
                <span class="spid-sr-only">Poste ID</span>
                <img src="{'spid-idp-posteid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-posteid.png'|ezimage(no)}'; this.onerror=null;"
                     alt="Poste ID"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="sielteid">
            <a href="{concat('deda/login/', 'sielte')|ezurl(no)}">
                <span class="spid-sr-only">Sielte ID</span>
                <img src="{'spid-idp-sielteid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-sielteid.png'|ezimage(no)}'; this.onerror=null;"
                     alt="Sielte ID"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="spiditalia">
            <a href="{concat('deda/login/', 'spiditalia')|ezurl(no)}">
                <span class="spid-sr-only">SPIDItalia Register.it</span>
                <img src="{'spid-idp-spiditalia.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-spiditalia.png'|ezimage(no)}'; this.onerror=null;"
                     alt="SpidItalia"/>
            </a>
        </li>
        <li class="spid-idp-button-link" data-idp="timid">
            <a href="{concat('deda/login/', 'tim')|ezurl(no)}">
                <span class="spid-sr-only">Tim ID</span>
                <img src="{'spid-idp-timid.svg'|ezimage(no)}"
                     onerror="this.src='{'spid-idp-timid.png'|ezimage(no)}'; this.onerror=null;"
                     alt="Tim ID"/>
            </a>
        </li>
        <li class="spid-idp-support-link" data-spidlink="info">
            <a href="https://www.spid.gov.it">Maggiori informazioni</a>
        </li>
        <li class="spid-idp-support-link" data-spidlink="rich">
            <a href="https://www.spid.gov.it/richiedi-spid">Non hai SPID?</a>
        </li>
        <li class="spid-idp-support-link" data-spidlink="help">
            <a href="https://www.spid.gov.it/serve-aiuto">Serve aiuto?</a>
        </li>
    </ul>
</div>

</body>
</html>
