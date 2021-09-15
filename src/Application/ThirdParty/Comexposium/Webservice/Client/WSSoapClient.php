<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Client;

class WSSoapClient extends \SoapClient
{
    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /**
     * @param string $username
     * @param string $password
     */
    public function setUsernameToken(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param string     $function_name
     * @param array      $arguments
     * @param array|null $options
     *
     * @throws \SoapFault
     *
     * @return mixed
     */
    public function call(string $function_name, array $arguments, ?array $options = null)
    {
        return parent::__soapCall(
            $function_name,
            $arguments,
            $options,
            $this->generateWSSecurityHeader()
        );
    }

    /**
     * @param string     $function_name
     * @param array      $arguments
     * @param array|null $options
     * @param array|null $output_headers
     *
     * @throws \SoapFault
     *
     * @return mixed
     */
    public function __soapCall(
        $function_name,
        $arguments,
        $options = null,
        $input_headers = null,
        &$output_headers = null
    ) {
        return parent::__soapCall(
            $function_name,
            $arguments,
            $options,
            $this->generateWSSecurityHeader(),
            $output_headers
        );
    }

    /**
     * @return string base64 encoded password digest
     */
    private function generateUsernameTokenXml(): string
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $rand = random_int(1000, 1000000);
        $nonce = pack('H*', $rand);
        $packedTimestamp = pack('a*', $timestamp);
        $packedPassword = pack('a*', $this->password);
        $hash = sha1($nonce . $packedTimestamp . $packedPassword);
        $packedHash = pack('H*', $hash);
        $passwordDigest = base64_encode($packedHash);

        return '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken wsu:Id="UsernameToken-1" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
                <wsse:Username>' . $this->username . '</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">' . $passwordDigest . '</wsse:Password>
                <wsse:Nonce EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary">' . base64_encode($nonce) . '</wsse:Nonce>
                <wsu:Created>' . $timestamp . '</wsu:Created>
            </wsse:UsernameToken>
        </wsse:Security>';
    }

    /**
     * @return \SoapHeader
     */
    private function generateWSSecurityHeader(): \SoapHeader
    {
        $usernameTokenXml = $this->generateUsernameTokenXml();

        $header = new \SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new \SoapVar($usernameTokenXml, XSD_ANYXML),
            true
        );

        return $header;
    }
}
