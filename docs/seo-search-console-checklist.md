# SEO + Google Search Console Checklist

## 1. Basisconfiguratie
- Controleer dat `APP_URL` in `.env` de productie-URL is.
- Controleer dat `APP_NAME` in `.env` correct staat.
- Controleer dat `https://overlijdens-berichten.nl/sitemap.xml` bereikbaar is.
- Controleer dat `public/robots.txt` de sitemap bevat en account/auth routes blokkeert.

## 2. Google Search Console instellen
- Voeg property toe als `Domeinproperty` (aanbevolen): `overlijdens-berichten.nl`.
- Rond DNS-verificatie af via TXT-record bij de domeinprovider.
- Voeg ook de URL-prefix property toe voor `https://overlijdens-berichten.nl/` voor snelle inspectie.

## 3. Sitemap indienen
- Ga naar `Indexering > Sitemaps`.
- Dien in: `sitemap.xml`.
- Controleer na indiening:
  - Status is `Succes`.
  - Aantal ontdekte URL's groeit mee met nieuwe berichten.

## 4. Indexering controleren
- Gebruik `URL-inspectie` voor:
  - Homepage
  - Een stadslandingspagina
  - Een blogartikel
  - Een overlijdensbericht detailpagina
- Vraag indexering aan voor nieuwe of bijgewerkte pagina's.

## 5. Canonical en robots valideren
- Controleer in page source van belangrijke pagina's:
  - `<link rel="canonical" ...>`
  - `<meta name="robots" ...>`
- Verwacht:
  - Publieke content: `index,follow`
  - Zoekresultaatpagina's met query (`?q=`): `noindex,follow`
  - Login/register/account/wizard: `noindex` (of `noindex,nofollow`)

## 6. Rich results en structured data
- Test homepage en detailpagina's in Rich Results Test.
- Controleer dat JSON-LD geldig is en geen kritieke fouten heeft.

## 7. Monitoring (wekelijks)
- Check `Prestaties` op:
  - Klikken, vertoningen, gemiddelde positie, CTR.
- Check `Pagina's` op:
  - `Geïndexeerd`
  - `Gecrawld - momenteel niet geïndexeerd`
  - `Gedetecteerd - momenteel niet geïndexeerd`
- Los technische fouten eerst op (robots/canonical/404/5xx).

## 8. Monitoring (maandelijks)
- Update bestaande SEO-titels/meta descriptions op pagina's met veel vertoningen en lage CTR.
- Voeg interne links toe van blog en stadslandingspagina's naar relevante overlijdensberichten.
- Controleer dat nieuwe berichten in sitemap verschijnen.
