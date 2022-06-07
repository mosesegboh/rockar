<?php
/**
 * @category        Peppermint
 * @package         Peppermint\Customer
 * @author          Craig Goodspeed <techteam@rockar.com>
 * @copyright       Copyright (c) 2020 Rockar, Ltd (http://rockar.com)
 */

use Aws\Credentials\CredentialProvider;
use Aws\S3\S3Client;

class Peppermint_Customer_Helper_Communication extends Mage_Core_Helper_Abstract
{
    /**
     * @var null|S3Client
     */
    private $client;

    /**
     * Function provides a manner to delay construction of the s3 client
     * That was if we want to set region or bucket we
     * @param string $region
     */
    private function createClient($region)
    {
        if (getenv('AWS_ACCESS_KEY_ID') && getenv('AWS_SECRET_ACCESS_KEY')) {
            //for development environment,
            $this->client = new S3Client([
                    'version' => 'latest',
                    'region' => $region,
                    'credentials' => CredentialProvider::env()
                ]
            );
        } else {
            //use role associated with ec2 instance.
            $this->client = new S3Client([
                    'version' => 'latest',
                    'region' => $region
                ]
            );
        }
    }

    /**
     * Send user document with metadata to AWS s3 bucket
     *
     * @param string $bucket, the bucket name
     * @param string $path, the path to where the object should be stored.
     * @param string $file, the contents to be uploaded -- should be the result of file_get_contents.
     * @param array $metadata, metadata to associate with this file, details required by SF.
     * @return \Aws\Result, the result of the putObject request.
     */
    public function sendToS3($bucket, $path, $file, $metadata, $region = null)
    {
        if (!$this->client) {
            $this->createClient($region ?? Mage::getStoreConfig('rockar_customer/documents/documents_send_to_s3_bucket_region'));
        }

        if (!$bucket) {
            $bucket = Mage::getStoreConfig('rockar_customer/documents/documents_send_to_s3_bucket_name');
        }

        return $this->client->putObject([
                'Body' => $file,
                'Key' => $path,
                'Bucket' => $bucket,
                'Metadata' => $metadata
            ]
        );
    }
}
