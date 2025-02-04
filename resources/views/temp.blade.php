$xmlString = '<?xml version="1.0" encoding="UTF-8"?>
            <Esp:Interface Version="1.0" xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/">
                <Esp:Event TerminalId="MSUGWE01" EventId="PROMPT_PRESENT_CARD" EventData=""/>
            </Esp:Interface>
            <?xml version="1.0" encoding="UTF-8"?>
            <Esp:Interface Version="1.0" xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/">
                <Esp:Event TerminalId="MSUGWE01" EventId="RF_FIELD_STATUS" EventData="NOT ACTIVE"/>
            </Esp:Interface>
            <?xml version="1.0" encoding="UTF-8"?>
            <Esp:Interface Version="1.0" xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/">
                <Esp:Event TerminalId="MSUGWE01" EventId="PROMPT_TRANSACTION_PROCESSING" EventData=""/>
            </Esp:Interface>
            <?xml version="1.0" encoding="UTF-8"?>
            <Esp:Interface Version="1.0" xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/">
                <Esp:Event TerminalId="MSUGWE01" EventId="PROMPT_TRANSACTION_OUTCOME" EventData="00"/>
            </Esp:Interface>
            <?xml version="1.0" encoding="UTF-8"?>
            <Esp:Interface Version="1.0" xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/">
                <Esp:Transaction Account="00" ActionCode="APPROVE" AmountTransactionFee="C0" AuthorizationProfile="11" BusinessDate="0204"
                CardNumber="910012******0959" CardProductName="ECOCASH" CurrencyCode="840" DateTime="0204134536" LocalDate="0204"
                LocalTime="154536" MerchantId="MSUGWERU0000001" MessageReasonCode="9790" PanEntryMode="01" PosCondition="00"
                PosDataCode="A1010119934C101" ReferralTelephone="MP250204.1545.K57393" ResponseCode="00" RetrievalRefNr="000274792251"
                TerminalId="MSUGWE01" TransactionAmount="1" TransactionId="396179" Type="PURCHASE">
                    <Esp:Balance AccountType="00" Amount="1" AmountType="53" CurrencyCode="840" Sign="D"></Esp:Balance>
                    <Esp:StructuredData DoNotPersist="FALSE" Name="EcoCashServerRefCode" PersistUntilAuthorized="FALSE"
                    Value="783540959250204154536592"></Esp:StructuredData>
                    <Esp:StructuredData DoNotPersist="FALSE" Name="EcoCashReference" PersistUntilAuthorized="FALSE"></Esp:StructuredData>
                </Esp:Transaction>
            </Esp:Interface>';

        // Regular expression to match individual XML documents
        preg_match_all('/<\?xml[^?]+\?>\s*<Esp:Interface[^>]*>.*?<\/Esp:Interface>/s', $xmlString, $matches);

        $xmlParts = $matches[0];

        foreach ($xmlParts as $index => $xml) {
            try {
                // Create a SimpleXMLElement object
                $xmlObject = new SimpleXMLElement($xml);

                // Check if the Esp:Transaction element exists
                if ($xmlObject->xpath('//Esp:Transaction')) {
                    echo "XML Document " . ($index + 1) . " contains Esp:Transaction:\n";
                    echo $xml . "\n\n";  // Print the matched XML document
                }
            } catch (Exception $e) {
                echo "Error parsing XML document " . ($index + 1) . ": " . $e->getMessage() . "\n";
            }
        }
