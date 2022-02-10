<?php
    class Morcommon{
        /*-----------------------------------------------------------------------------------
         *------------------------------ standAloneAppConnect -------------------------------
         *-----------------------------------------------------------------------------------
         *
         * Global routine that facilitates the connection to Mor's GERS database
         *
         * @return mixed $db connection object from IDBResource, or false if there is an error
         * @see IDBResource
         */
        public function standAloneAppConnect() {
            global $appconfig;

            $db = new IDBResource($appconfig['dbhost'], $appconfig['dbuser'], $appconfig['dbpwd'],  $appconfig['dbname']);
            
            try {
                $db->open();
            }
            catch (Exception $e) {
                    $errmsg   = 'Invalid Username/Password';
                        
                    return false; 
            }

            return $db;

        }

        /*********************************************************************************************************************************************
        /*********************************************************************************************************************************************
        /*********************************************************************************************************************************************
         * * generateCSV: 
         * *    Function will generate a csv file on the path provided with the array of data 
         * * Arguments: 
         * *    outpath: Path where the file writes   
         * *    filename: Name of the file 
         * *    data: (Array) data of array 
         * *
         * * Return: Boolean true for succesfule else false 
         * *
         *********************************************************************************************************************************************
         *********************************************************************************************************************************************
         *********************************************************************************************************************************************/
        function generateCSV( $outpath,  $filename, $header, $data ){
            global $appconfig, $logger;
            
            try{ 
                $logger->debug( "Writing csv file to: " . $outpath . $filename );
                $handle = fopen( $outpath . $filename, 'a+' );

                //Write header 
                fputcsv( $handle, $header );

                foreach( $data as $value ){
                    fputcsv( $handle, $value );
                }
                fclose( $handle );
                $logger->debug( "CSV file created" );
                return true;
            }
            catch( Exception $e ){
                $logger->error( "Could not create csv file" );
                return false;
            }

        }

        /*********************************************************************************************************************************************
        /*********************************************************************************************************************************************
        /*********************************************************************************************************************************************
         * * email: 
         * *    Function will email 
         * * Arguments: 
         * *    host: (Array) with following keys and values   
         * *        - Host  
         * *        - Port
         * *    to: (Array) Filled with email values
         * *        - Emails 
         * *    from: (Array) 
         * *        - From: Email
         * *        - Name: Name of email 
         * *    Attachments: (Array)
         * *        - Value of file paths
         * *    Message: (String)
         * *       - Body of email message
         * *
         * * Return: Boolean true for succes else otherwise
         * *
         *********************************************************************************************************************************************
         *********************************************************************************************************************************************
         *********************************************************************************************************************************************/
        function email( $host, $to, $from, $attachments, $message ){
            global $logger;

            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = $host['host'];
            $mail->Port = $host['port'];
            $mail->From =  $from['from'];
            $mail->FromName = $from['name'];

            foreach( $to as $recipient ){
                $mail->addAddress($recipient); //should go to finance@morfurniture.com
            }
            $mail->addReplyTo('');
            $mail->WordWrap = 50;
            
            foreach( $attachments as $attachment ){
                $mail->addAttachment($attachment);
            }

            $mail->isHTML(true);
            $mail->Subject = $message['subject'];
            $mail->Body    = $message['body'];

            if(!$mail->send()) {
                $logger->debug( 'Mailer Error: ' . $mail->ErrorInfo );
                return false;
            } 
            else {
                $logger->debug( 'Message has been sent' );
                return true;
            }
            return true;
        }
    }
?>
