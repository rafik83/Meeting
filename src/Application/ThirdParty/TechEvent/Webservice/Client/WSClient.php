<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Client;

use Proximum\Vimeet\Application\Adapter\HttpAdapterInterface;
use Proximum\Vimeet\Application\Exception\Adapter\Http\ServerErrorException;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Data\Type;

class WSClient
{
    /** @var HttpAdapterInterface */
    private $httpAdapter;

    public function __construct(HttpAdapterInterface $httpAdapter)
    {
        $this->httpAdapter = $httpAdapter;
    }

    public function getContactsToSynchro(string $endpoint, string $pIdAuth): array
    {
        try {

            $response = $this->httpAdapter->post($endpoint, [], ['pIdAuth' => $pIdAuth]);

            if ($response->statusCode === 200) {
                $body = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n
<DataTable xmlns=\"https://www.mybadgeonline.com/Euronaval/WS/Contacts.asmx\">\r\n
  <xs:schema id=\"NewDataSet\" xmlns=\"\" xmlns:xs=\"http://www.w3.org/2001/XMLSchema\" xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\">\r\n
    <xs:element name=\"NewDataSet\" msdata:IsDataSet=\"true\" msdata:MainDataTable=\"Results\" msdata:UseCurrentLocale=\"true\">\r\n
      <xs:complexType>\r\n
        <xs:choice minOccurs=\"0\" maxOccurs=\"unbounded\">\r\n
          <xs:element name=\"Results\">\r\n
            <xs:complexType>\r\n
              <xs:sequence>\r\n
                <xs:element name=\"IDCONTACT\" type=\"xs:int\" minOccurs=\"0\" />\r\n
                <xs:element name=\"NOM\" type=\"xs:string\" minOccurs=\"0\" />\r\n
                <xs:element name=\"PRENOM\" type=\"xs:string\" minOccurs=\"0\" />\r\n
                <xs:element name=\"EMAIL\" type=\"xs:string\" minOccurs=\"0\" />\r\n
                <xs:element name=\"SOCIETE\" type=\"xs:string\" minOccurs=\"0\" />\r\n
              </xs:sequence>\r\n
            </xs:complexType>\r\n
          </xs:element>\r\n
        </xs:choice>\r\n
      </xs:complexType>\r\n
    </xs:element>\r\n
  </xs:schema>\r\n
  <diffgr:diffgram xmlns:msdata=\"urn:schemas-microsoft-com:xml-msdata\" xmlns:diffgr=\"urn:schemas-microsoft-com:xml-diffgram-v1\">\r\n
    <DocumentElement xmlns=\"\">\r\n
      <Results diffgr:id=\"Results1\" msdata:rowOrder=\"0\">\r\n
        <IDCONTACT>126891020</IDCONTACT>\r\n
        <NOM>XXDERNIERTESTSNCF</NOM>\r\n
        <PRENOM>FANNY</PRENOM>\r\n
        <EMAIL>bbb@bbbb.fr</EMAIL>\r\n
        <SOCIETE>TECHEVENT</SOCIETE>\r\n
      </Results>\r\n
      <Results diffgr:id=\"Results2\" msdata:rowOrder=\"1\">\r\n
        <IDCONTACT>126958341</IDCONTACT>\r\n
        <NOM>XXDERNIERMODIF</NOM>\r\n
        <PRENOM>XXFANNY</PRENOM>\r\n
        <EMAIL>aaaa@aaaa.fr</EMAIL>\r\n
        <SOCIETE>TECHEVENT</SOCIETE>\r\n
      </Results>\r\n
    </DocumentElement>\r\n
  </diffgr:diffgram>\r\n
</DataTable>";
                $xml = simplexml_load_string($response->body);

                if (false === $xml) {
                    return [];
                }

                $contacts = [];
                foreach ($xml->xpath('*/DocumentElement/Results') as $xmlContact) {
                    $json = json_encode($xmlContact);
                    $contact = json_decode($json, true);

                    $contacts[$contact[Type::ID_CONTACT]] = $contact;
                }

                return $contacts;
            }
        } catch (ServerErrorException $error) {
            return [];
        }
    }
}
