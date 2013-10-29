<?php
/**
 * TOP API: taobao.travel.itemarea.get request
 * 
 * @author auto create
 * @since 1.0, 2013-09-01 16:44:52
 */
class TravelItemareaGetRequest
{
	/** 
	 * 商品所属类目ID。旅游线路商品有两个值：1(国内线路类目ID)，2(国际线路类目ID)。
	 **/
	private $cid;
	
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

	public function getApiMethodName()
	{
		return "taobao.travel.itemarea.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->cid,"cid");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
