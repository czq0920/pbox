<?php
namespace app\common\common;
Vendor('ali_mns.mns-autoloader');
use AliyunMNS\Client;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\PublishMessageRequest;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Exception\MnsException;

class Mns {
	private $accessId;
	private $accessKey;
	private $endPoint;
	private $client;

	public function __construct($accessId, $accessKey, $endPoint)
	{
		$this->accessId = $accessId;
		$this->accessKey = $accessKey;
		$this->endPoint = $endPoint;
	}

	public static function getAccessInfo() {
		$accessId = "pxSEpUL4UbURat4n";
		$accessKey = "dsm3tOAUUvEVGXnfc2Hmmn9Wytfxv1";
		//$endPoint = "http://31373024.mns.cn-beijing-internal.aliyuncs.com/";
		$endPoint = "http://31373024.mns.cn-beijing.aliyuncs.com/";
		return array('accessId'=>$accessId,
			     'accessKey'=>$accessKey,
			     'endPoint'=>$endPoint);
	}

	public function publishMessage($topicName, $message) {
		$this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
		$topic = $this->client->getTopicRef($topicName);
		$request = new PublishMessageRequest($message);
		try {
			$res = $topic->publishMessage($request);
		} catch (MnsException $e) {
			echo "publishMessage failed: " . $e . "\n";
			return false;
		}
		return true;
	}
}
?>
