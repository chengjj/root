<?php
/**
 * TOP API: taobao.travel.item.add request
 * 
 * @author auto create
 * @since 1.0, 2013-09-01 16:44:52
 */
class TravelItemAddRequest
{
	/** 
	 * 商品上传后的状态。可选值:onsale(出售中),instock(仓库中);默认值:onsale。
	 **/
	private $approveStatus;
	
	/** 
	 * 商品的积分返点比例。如:5,表示:返点比例0.5%. 注意：返点比例必须是>0的整数.B商家在发布非虚拟商品时，这个字段必须设置，返点必须是 5的倍数，即0.5%的倍数。注意该字段值最高值不超过500，即50%。
	 **/
	private $auctionPoint;
	
	/** 
	 * 商品所属类目ID。发布旅游线路商品有两个值：1(国内线路类目ID)，2(国际线路类目ID)。
	 **/
	private $cid;
	
	/** 
	 * 宝贝所在地城市。如杭州 。可以通过http://dl.open.taobao.com/sdk/商品城市列表.rar查询 ，该字段为必填字段
	 **/
	private $city;
	
	/** 
	 * 商品描述，不超过50000个字符。
	 **/
	private $desc;
	
	/** 
	 * 最晚成团提前天数，最小0天，最大为180天。
	 **/
	private $duration;
	
	/** 
	 * 费用包含，不超过1500个字符。
	 **/
	private $feeInclude;
	
	/** 
	 * 费用不包，不超过1500个字符。
	 **/
	private $feeNotInclude;
	
	/** 
	 * 支持会员打折。可选值:true,false;默认值:false(不打折)。
	 **/
	private $hasDiscount;
	
	/** 
	 * 是否有发票。可选值:true,false (商城卖家此字段必须为true);默认值:false(无发票)。
	 **/
	private $hasInvoice;
	
	/** 
	 * 橱窗推荐。可选值:true,false;默认值:false(不推荐)，B商家不用设置该字段，均为true。
	 **/
	private $hasShowcase;
	
	/** 
	 * 商品主图。类型:JPG,GIF;最大长度:500k，支持的文件类型：gif,jpg,jpeg,png。
	 **/
	private $image;
	
	/** 
	 * 是否为铁定出游商品的参数
设置为true，那么该商品为铁定出游商品；
设置为false，那么该商品为非铁定出游商品。
默认为false
	 **/
	private $isTdcy;
	
	/** 
	 * 定时上架时间。(时间格式：yyyy-MM-dd HH:mm:ss)。
	 **/
	private $listTime;
	
	/** 
	 * 商品库存。如果发布旅游度假线路宝贝，该字段可以忽略。
	 **/
	private $num;
	
	/** 
	 * 预定须知，不超过1500个字符。
	 **/
	private $orderInfo;
	
	/** 
	 * 商家的外部编码，最大为512字节。
	 **/
	private $outerId;
	
	/** 
	 * 商品主图需要关联的图片空间的相对url。这个url所对应的图片必须要属于当前用户。pic_path和image只需要传入一个,如果两个都传，默认选择pic_path。
	 **/
	private $picPath;
	
	/** 
	 * 玩法描述，已经废弃，不用填写。
	 **/
	private $playDesc;
	
	/** 
	 * 线路玩法id，已经废弃，不用填写。
	 **/
	private $playId;
	
	/** 
	 * 商品一口价,以“分”为单位。如果发布旅游度假线路的宝贝，该字段可以忽略。
	 **/
	private $price;
	
	/** 
	 * Json串，价格日历信息（针对旅游度假线路的销售属性），定义了2012年6月8号成人价，儿童价，单房差均为10元，库存为100的日历价格。price_calendar属性中一个{}中表示1天的价格日历信息，可以最多输入90天的价格日历，最小和最大日期不能跨度超过90天。其中，"man_num"：成人名额； "man_price"：成人价格，以分为单位；"child_num"：儿童名额；"child_price"：儿童价格，以分为单位；"diff_price"：单人房差，以分为单位。
	 **/
	private $priceCalendar;
	
	/** 
	 * 商品属性列表。格式为：pid:vid;pid:vid。例如发布度假线路商品，那么这里就需要填写：出发地属性id:城市id;目的地市属性id:目的地市id;……等等。
	 **/
	private $props;
	
	/** 
	 * 宝贝所在地省份。如浙江，具体可以下载http://dl.open.taobao.com/sdk/商品城市列表.rar 取到，该字段为必填字段
	 **/
	private $prov;
	
	/** 
	 * 退改规定，不超过1500个字符。
	 **/
	private $refundRegulation;
	
	/** 
	 * 商品秒杀三个值：可选类型web_only(只能通过web网络秒杀)，wap_only(只能通过wap网络秒杀)，web_and_wap(既能通过web秒杀也能通过wap秒杀)。
	 **/
	private $secondKill;
	
	/** 
	 * 关联商品与店铺类目，结构:",cid1,cid2,...,"，如果店铺类目存在二级类目，必须传入子类目cids。
	 **/
	private $sellerCids;
	
	/** 
	 * sku销售属性串对应的价格，精确到分，每一个属性串都会对应一个价格，单位为分。sku_prices的数组大小应该和sku_properties的数组大小一致。如果发布线路的商品，该字段可以忽略。
	 **/
	private $skuPrices;
	
	/** 
	 * sku销售属性串，调用taobao.travel.itemprops.get接口获取商品销售属性code，然后拼接成pid:vid;pid:vid格式。如果发布线路的商品，该字段可以忽略。
	 **/
	private $skuProperties;
	
	/** 
	 * sku销售属性串对应的库存，每一个属性串都会对应一个库存，显然sku_quantities的数组大小应该和sku_properties的数组大小一致。如果发布线路的商品，该字段可以忽略。
	 **/
	private $skuQuantities;
	
	/** 
	 * 商品是否支持拍下减库存:1支持;2取消支持(付款减库存);0(默认)不更改，集市卖家默认拍下减库存;商城卖家默认付款减库存。
	 **/
	private $subStock;
	
	/** 
	 * 商品标题。不能超过60个字节或者30个汉字
	 **/
	private $title;
	
	private $apiParas = array();
	
	public function setApproveStatus($approveStatus)
	{
		$this->approveStatus = $approveStatus;
		$this->apiParas["approve_status"] = $approveStatus;
	}

	public function getApproveStatus()
	{
		return $this->approveStatus;
	}

	public function setAuctionPoint($auctionPoint)
	{
		$this->auctionPoint = $auctionPoint;
		$this->apiParas["auction_point"] = $auctionPoint;
	}

	public function getAuctionPoint()
	{
		return $this->auctionPoint;
	}

	public function setCid($cid)
	{
		$this->cid = $cid;
		$this->apiParas["cid"] = $cid;
	}

	public function getCid()
	{
		return $this->cid;
	}

	public function setCity($city)
	{
		$this->city = $city;
		$this->apiParas["city"] = $city;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function setDesc($desc)
	{
		$this->desc = $desc;
		$this->apiParas["desc"] = $desc;
	}

	public function getDesc()
	{
		return $this->desc;
	}

	public function setDuration($duration)
	{
		$this->duration = $duration;
		$this->apiParas["duration"] = $duration;
	}

	public function getDuration()
	{
		return $this->duration;
	}

	public function setFeeInclude($feeInclude)
	{
		$this->feeInclude = $feeInclude;
		$this->apiParas["fee_include"] = $feeInclude;
	}

	public function getFeeInclude()
	{
		return $this->feeInclude;
	}

	public function setFeeNotInclude($feeNotInclude)
	{
		$this->feeNotInclude = $feeNotInclude;
		$this->apiParas["fee_not_include"] = $feeNotInclude;
	}

	public function getFeeNotInclude()
	{
		return $this->feeNotInclude;
	}

	public function setHasDiscount($hasDiscount)
	{
		$this->hasDiscount = $hasDiscount;
		$this->apiParas["has_discount"] = $hasDiscount;
	}

	public function getHasDiscount()
	{
		return $this->hasDiscount;
	}

	public function setHasInvoice($hasInvoice)
	{
		$this->hasInvoice = $hasInvoice;
		$this->apiParas["has_invoice"] = $hasInvoice;
	}

	public function getHasInvoice()
	{
		return $this->hasInvoice;
	}

	public function setHasShowcase($hasShowcase)
	{
		$this->hasShowcase = $hasShowcase;
		$this->apiParas["has_showcase"] = $hasShowcase;
	}

	public function getHasShowcase()
	{
		return $this->hasShowcase;
	}

	public function setImage($image)
	{
		$this->image = $image;
		$this->apiParas["image"] = $image;
	}

	public function getImage()
	{
		return $this->image;
	}

	public function setIsTdcy($isTdcy)
	{
		$this->isTdcy = $isTdcy;
		$this->apiParas["is_tdcy"] = $isTdcy;
	}

	public function getIsTdcy()
	{
		return $this->isTdcy;
	}

	public function setListTime($listTime)
	{
		$this->listTime = $listTime;
		$this->apiParas["list_time"] = $listTime;
	}

	public function getListTime()
	{
		return $this->listTime;
	}

	public function setNum($num)
	{
		$this->num = $num;
		$this->apiParas["num"] = $num;
	}

	public function getNum()
	{
		return $this->num;
	}

	public function setOrderInfo($orderInfo)
	{
		$this->orderInfo = $orderInfo;
		$this->apiParas["order_info"] = $orderInfo;
	}

	public function getOrderInfo()
	{
		return $this->orderInfo;
	}

	public function setOuterId($outerId)
	{
		$this->outerId = $outerId;
		$this->apiParas["outer_id"] = $outerId;
	}

	public function getOuterId()
	{
		return $this->outerId;
	}

	public function setPicPath($picPath)
	{
		$this->picPath = $picPath;
		$this->apiParas["pic_path"] = $picPath;
	}

	public function getPicPath()
	{
		return $this->picPath;
	}

	public function setPlayDesc($playDesc)
	{
		$this->playDesc = $playDesc;
		$this->apiParas["play_desc"] = $playDesc;
	}

	public function getPlayDesc()
	{
		return $this->playDesc;
	}

	public function setPlayId($playId)
	{
		$this->playId = $playId;
		$this->apiParas["play_id"] = $playId;
	}

	public function getPlayId()
	{
		return $this->playId;
	}

	public function setPrice($price)
	{
		$this->price = $price;
		$this->apiParas["price"] = $price;
	}

	public function getPrice()
	{
		return $this->price;
	}

	public function setPriceCalendar($priceCalendar)
	{
		$this->priceCalendar = $priceCalendar;
		$this->apiParas["price_calendar"] = $priceCalendar;
	}

	public function getPriceCalendar()
	{
		return $this->priceCalendar;
	}

	public function setProps($props)
	{
		$this->props = $props;
		$this->apiParas["props"] = $props;
	}

	public function getProps()
	{
		return $this->props;
	}

	public function setProv($prov)
	{
		$this->prov = $prov;
		$this->apiParas["prov"] = $prov;
	}

	public function getProv()
	{
		return $this->prov;
	}

	public function setRefundRegulation($refundRegulation)
	{
		$this->refundRegulation = $refundRegulation;
		$this->apiParas["refund_regulation"] = $refundRegulation;
	}

	public function getRefundRegulation()
	{
		return $this->refundRegulation;
	}

	public function setSecondKill($secondKill)
	{
		$this->secondKill = $secondKill;
		$this->apiParas["second_kill"] = $secondKill;
	}

	public function getSecondKill()
	{
		return $this->secondKill;
	}

	public function setSellerCids($sellerCids)
	{
		$this->sellerCids = $sellerCids;
		$this->apiParas["seller_cids"] = $sellerCids;
	}

	public function getSellerCids()
	{
		return $this->sellerCids;
	}

	public function setSkuPrices($skuPrices)
	{
		$this->skuPrices = $skuPrices;
		$this->apiParas["sku_prices"] = $skuPrices;
	}

	public function getSkuPrices()
	{
		return $this->skuPrices;
	}

	public function setSkuProperties($skuProperties)
	{
		$this->skuProperties = $skuProperties;
		$this->apiParas["sku_properties"] = $skuProperties;
	}

	public function getSkuProperties()
	{
		return $this->skuProperties;
	}

	public function setSkuQuantities($skuQuantities)
	{
		$this->skuQuantities = $skuQuantities;
		$this->apiParas["sku_quantities"] = $skuQuantities;
	}

	public function getSkuQuantities()
	{
		return $this->skuQuantities;
	}

	public function setSubStock($subStock)
	{
		$this->subStock = $subStock;
		$this->apiParas["sub_stock"] = $subStock;
	}

	public function getSubStock()
	{
		return $this->subStock;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		$this->apiParas["title"] = $title;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getApiMethodName()
	{
		return "taobao.travel.item.add";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkMaxValue($this->auctionPoint,500,"auctionPoint");
		RequestCheckUtil::checkMinValue($this->auctionPoint,0,"auctionPoint");
		RequestCheckUtil::checkNotNull($this->cid,"cid");
		RequestCheckUtil::checkNotNull($this->desc,"desc");
		RequestCheckUtil::checkMaxLength($this->desc,50000,"desc");
		RequestCheckUtil::checkNotNull($this->duration,"duration");
		RequestCheckUtil::checkMaxValue($this->duration,180,"duration");
		RequestCheckUtil::checkMinValue($this->duration,0,"duration");
		RequestCheckUtil::checkNotNull($this->feeInclude,"feeInclude");
		RequestCheckUtil::checkMaxLength($this->feeInclude,1500,"feeInclude");
		RequestCheckUtil::checkNotNull($this->feeNotInclude,"feeNotInclude");
		RequestCheckUtil::checkMaxLength($this->feeNotInclude,1500,"feeNotInclude");
		RequestCheckUtil::checkNotNull($this->orderInfo,"orderInfo");
		RequestCheckUtil::checkMaxLength($this->orderInfo,1500,"orderInfo");
		RequestCheckUtil::checkMaxLength($this->outerId,512,"outerId");
		RequestCheckUtil::checkMaxLength($this->playDesc,1500,"playDesc");
		RequestCheckUtil::checkNotNull($this->priceCalendar,"priceCalendar");
		RequestCheckUtil::checkNotNull($this->props,"props");
		RequestCheckUtil::checkNotNull($this->refundRegulation,"refundRegulation");
		RequestCheckUtil::checkMaxLength($this->refundRegulation,1500,"refundRegulation");
		RequestCheckUtil::checkMaxListSize($this->sellerCids,20,"sellerCids");
		RequestCheckUtil::checkMaxLength($this->sellerCids,256,"sellerCids");
		RequestCheckUtil::checkMaxListSize($this->skuPrices,380,"skuPrices");
		RequestCheckUtil::checkMaxListSize($this->skuProperties,380,"skuProperties");
		RequestCheckUtil::checkMaxListSize($this->skuQuantities,380,"skuQuantities");
		RequestCheckUtil::checkMaxValue($this->subStock,2,"subStock");
		RequestCheckUtil::checkMinValue($this->subStock,0,"subStock");
		RequestCheckUtil::checkNotNull($this->title,"title");
		RequestCheckUtil::checkMaxLength($this->title,60,"title");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
