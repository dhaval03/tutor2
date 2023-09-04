<?php 
//require(DIR_SYSTEM.'library/AWS/aws-autoloader.php');
namespace Opencart\System\Library\AWS;
class S3{
	private $accessKey = "";
	private $secretKey = "";
	private $region = "us-east-1";
	private $s3;
	function __construct($config,$region){
		$this->config = $config;
		$this->accessKey = $this->config->get('config_amazon_accesskey');
		$this->secretKey = $this->config->get('config_amazon_secretkey');
		$this->region = $region;
		$this->s3 = new \Aws\S3\S3Client([
			'region'  => $this->region,
			'version' => 'latest',
			'credentials' => [
				'key'    => $this->accessKey,
				'secret' => $this->secretKey,
			],
			'http'    => [
				'verify' =>false
			]
		]);
	}
	public function putObject($fileName,$bucket,$file){
		$result = $this->s3->putObject([
			'Bucket' => $bucket,
			'Key'    => $fileName,
			'Body'   => fopen($file, 'r'),
			'ACL'    => 'public-read'
			//'SourceFile' => $file
		]);
		return $result;
	}
	public function getObject($fileName,$bucket){
		$result = $this->s3->getObject([
			'Bucket' => $bucket,
			'Key'    => $fileName
			//'Body'   => fopen($file, 'r'),
			//'ACL'    => 'public-read'
			//'SourceFile' => $file
		]);
		return $result;
	}
	public function deleteObject($fileName,$bucket){
		$result = $this->s3->deleteObject([
			'Bucket' => $bucket,
			'Key'    => $fileName
		]);
		return $result;
	}
}
?>