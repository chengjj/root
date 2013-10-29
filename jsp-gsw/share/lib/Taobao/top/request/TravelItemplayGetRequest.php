<?php
/**
 * TOP API: taobao.travel.itemplay.get request
 * 
 * @author auto create
 * @since 1.0, 2013-09-01 16:44:52
 */
class TravelItemplayGetRequest
{
	/** 
	 * 商品所属类目ID。旅游线路商品有两个值：1(国内线路类目ID)，2(国际线路类目ID)
	 **/
	private $cid;
	
	/** 
	 * 目的地code列表，多个目的地code以“,”分隔
	 **/
	private $destCodes;
	
	/** 
	 * 玩法类型，1跟团游、2自由行
	 **/
	private $playType;
	
	private $apiParas = array();
	
	public function setCid($cid)
	{
		$this->cid = $cid;
		$this->apiParas["cid"] = $cid;
	}

	public function getCid()
	{
		return $this->cid;
	}

	public function setDestCodes($destCodes)
	{
		$this->destCodes = $destCodes;
		$this->apiParas["dest_codes"] = $destCodes;
	}

	public function getDestCodes()
	{
		return $this->destCodes;
	}

	public function setPlayType($playType)
	{
		$this->playType = $playType;
		$this->apiParas["play_type"] = $playType;
	}

	public function getPlayType()
	{
		return $this->playType;
	}

	public function getApiMethodName()
	{
		return "taobao.travel.itemplay.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->cid,"cid");
		RequestCheckUtil::checkNotNull($this->destCodes,"destCodes");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
