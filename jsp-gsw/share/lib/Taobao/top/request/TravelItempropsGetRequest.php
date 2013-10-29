<?php
/**
 * TOP API: taobao.travel.itemprops.get request
 * 
 * @author auto create
 * @since 1.0, 2013-09-01 16:44:52
 */
class TravelItempropsGetRequest
{
	/** 
	 * 商品所属类目ID。旅游线路商品有两个值：1(国内线路类目ID)，2(国际线路类目ID)。
	 **/
	private $cid;
	
	/** 
	 * 属性id (取某个类目属性时，传pid；若不传该字段，返回该类目下所有属性)。
	 **/
	private $pid;
	
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

	public function setPid($pid)
	{
		$this->pid = $pid;
		$this->apiParas["pid"] = $pid;
	}

	public function getPid()
	{
		return $this->pid;
	}

	public function getApiMethodName()
	{
		return "taobao.travel.itemprops.get";
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
