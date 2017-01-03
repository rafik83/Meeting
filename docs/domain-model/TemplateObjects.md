# Les objets de template (TEMPLATE OBJECTS)

## Hiérarchie des classes d'objets 

```

                                   *********************************************
                      _____________*            TEMPLATE OBJECT                *____________
                     |             *********************************************            |
                     |                                |                                     |
                     |                                |                                     |
    *********************************    *********************************   *********************************
    *       EDITABLE OBJECT         *    *    - Text                     *   *        ItemCollection         *
    *********************************    *    - MediaCollection          *   *********************************
                     |                   *    - Participant              *                  |
                     |                   *********************************                  |
    *********************************                                        ********************************* 
    *   - Url                       *                                        *        TagsCollection         *
    *   - Country                   *                                        *********************************
    *   - ButtonLink                *
    *   - Gender                    *
    *   - Telephone                 *
    *   - Image                     *
    *   - BooleanObject             *
    *   - Nomenclature              *
    *   - EditableText              *
    *********************************

```

## Utilisation

### Récupérer tous les objets de template d'une fiche de participation (`Sheet`):

```
    use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
    
    // ...
    
    $sheet = ...;                  // Instance de Domain\Sheet
    $nomenclatureRepository = ...; // Implémentation de NomenclatureRepositoryInterface
    $locale = 'fr';
    $factory = new TemplateDataFactory($nomenclatureRepository);
    
    // Données de présentation : 
    $data = $factory->createFromSheet($sheet, $locale);
    $templateObjects = $data->getObjects();
    
    // Données d'inscription (`registration`) :
    $registrationData = $factory->createRegistrationFromSheet($sheet, $locale);
    $templateObjects = $data->getObjects();
    
    // Do something with $templateObjects
    
```


