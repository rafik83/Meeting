# Tech Event Mapping

## Configuration
Le mapping Tech Event a la forme suivante :

```json
{
  "endpoint": "https://www.example.net/EVENEMENT/Ws/Contacts.asmx/GetContactsToSynchro?pidAuth=AUTH",
  "type": "TYPE_ID",
  "mandatory_keys": {
    "email": "Email",
    "identifier": "IdContact",
    "identifierMD5": "IdContactMD5",
    "country": "Pays",
    "loginData": "Password"
  },
  "mapping": {
    "IdCivilite": "participant_gender",
    "Nom": "participant_lastname",
    "Prenom": "participant_firstname",
    "Portable": "participant_mobile",
    "Tel": "participant_phone",
    "Email": "email",
    "Grade": "participant_address",
    "Fonction": "participant_position",
    "Societe": "sheet_title",
    "Adresse1": "sheet_address",
    "CodePostal": "sheet_zipcode",
    "Ville": "sheet_city",
    "Pays": "sheet_country",
    "TypeSociete": "sheet_organization_category",
    "Taille": "sheet_organization_staff",
    "Service": "sheet_generic_tag_1",
    "SousActivite2": "sheet_template_generic_tag_2"
  },
  "normalize": {
    "B2B": "boolean",
    "Portable": "telephone",
    "Tel": "telephone",
    "IdCivilite": "gender",
    "Pays": "country"
  }
}
```

L'entrée "endpoint" est l'endpoint de l'api Tech Event. Il contient l'pidAuth qui permet d'authentifier l'appel.

L'entrée "type" permet de définir le type vers lequel sera convertie le contact reçu.

Le champ "mandatory_keys" est obligatoire et permet de faire le lien entre les champs indispensables sur vimeet et leur équivalent chez Tech Event.
Les clés importantes sont :
```
"email"
"identifier"
"identifierMD5"
"country"
```
Ce champ permet de faire l'équivalence en fonction de l'api TechEvent car la casse peut changer d'un événement à l'autre de leur côté ("Email", "email", "IDCONTACT", "IdContact", etc...).

L'entrée "mapping" a pour but de faire le lien entre un champ TechEvent et son association en tag sur Vimeet.

L'entrée "normalize" a pour but de définir les champs de l'api Tech Event qui doivent être normalisés pour être compatible Vimeet.
La liste des `Converter` est disponible dans le repertoire : `src/Application/ThirdParty/TechEvent/Webservice/Converter`


## Nouvelle configuration

Afin de pouvoir faire un dispatch entre différent type de participation en fonction des informations que l'on reçoit d'un contact. La configuration doit changer. Nouvelle configuration :

```json
{
  "endpoint": "https://www.example.net/EVENEMENT/Ws/Contacts.asmx/GetContactsToSynchro?pidAuth=AUTH",
  "mandatory_keys": {
    "email": "Email",
    "identifier": "IdContact",
    "identifierMD5": "IdContactMD5",
    "country": "Pays",
    "loginData": "Password"
  },
  "types": {
    "TYPE_ID" : {
      "condition": "'IdCategorie' === 'J'",
      "mapping": {
        "IdCivilite": "participant_gender",
        "Nom": "participant_lastname",
        "Prenom": "participant_firstname",
        "Portable": "participant_mobile",
        "Tel": "participant_phone",
        "Email": "email",
        "Grade": "participant_address",
        "Fonction": "participant_position",
        "Societe": "sheet_title",
        "Adresse1": "sheet_address",
        "CodePostal": "sheet_zipcode",
        "Ville": "sheet_city",
        "Pays": "sheet_country",
        "TypeSociete": "sheet_organization_category",
        "Taille": "sheet_organization_staff",
        "Service": "sheet_generic_tag_1",
        "SousActivite2": "sheet_template_generic_tag_2"
      }
    }
  },
  "normalize": {
    "B2B": "boolean",
    "Portable": "telephone",
    "Tel": "telephone",
    "IdCivilite": "gender",
    "Pays": "country"
  }
}
```
