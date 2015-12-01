<?php

/*
 © Copyright Webgility LLC 2010
    ----------------------------------------
 All materials contained in these files are protected by United States copyright
 law and may not be reproduced, distributed, transmitted, displayed, published or
 broadcast without the prior written permission of Webgility LLC. You may not
 alter or remove any trademark, copyright or other notice from copies of the
 content.
 File last updated: 04/01/2012
*/

class WgCommon
{	
	public function parseRequest()
	{

		$request ='';
		if($_SERVER['REQUEST_METHOD']=='GET') {
			$request = stripslashes($_REQUEST['request']);			
		} elseif($_SERVER['REQUEST_METHOD']=='POST') {
			$request = 	trim($this->getRequestData(str_replace(' ', '+', stripslashes($_POST['request']))));
			$request = substr($request,strpos($request, "{"),strrpos($request, "}")+1);
		}
		
		
		#{"method":"getOrders","username":"nehau@webgility.com","password":"lcuser140","datefrom":"2000-09-07 00:00:00 AM","start_order_no":"0","ecc_excl_list":"All","order_per_response":"25","storeid":"1"}
		#{"method":"getOrders","username":"nehau@webgility.com","password":"lcuser140","datefrom":"1990-07-27 00:00:00 AM","start_order_no":"0","ecc_excl_list":"All","order_per_response":"25","storeid":"1"}
		
		if($request) {

			$request = json_decode($request,true);
			foreach($request as $k=>$v)
			{
				$$k = $v;
			}
		}

		$others = isset($others) ? $others:"";
		$itemid = isset($itemid) ? $itemid:"";
		if(!empty($method))
		{ 
			switch ($method)
			{
				case 'checkAccessInfo':
				case 'isAuthorized':
				echo $str = $this->$method($username,$password,$others='');
				break;
				
				case 'getStores':
				echo $str = $this->$method($username,$password,$devicetokenid);
				break;

				case 'getItems':
				//echo $str =$this->$method($username,$password,$start_item_no,$limit,$datefrom,$storeid,$others);
				//echo $str =$this->$method($username,$password,$datefrom,$start,$limit,$storeid=1,$others);
				echo $str =$this->$method($username,$password,$datefrom,$start,$limit,$storeid=1);
				break;

				case 'getAttributesets':
				case 'getShippingMethods':
				case 'getManufacturers':
				case 'getTaxes':
				case 'getOrderStatusForOrder':
				case 'getShippingMethods':
				case 'getPaymentMethods':
				case 'getCompanyInfo':
				case 'getOrderStatus':
				case 'getCategory':
				case 'getItemsQuantity':
				echo $str = $this->$method($username,$password,$storeid,$others);
				break;

				case 'getOrders':
				echo $str = $this->$method($username,$password,$datefrom,$start_order_no,$ecc_excl_list,$order_per_response,$storeid,$devicetokenid);
				break;

				case 'synchronizeItems':
				echo $str = $this->$method($username,$password,$data,$storeid,$others);
				break;

				case 'ItemUpdatePriceQty':
				echo $str = $this->$method($username,$password,$itemId,$qty,$price,$cost,$weight,$storeid=1);
				break;
				
				case 'OrderUpdateStatus':
				//echo $str = $this->method($username,$password,$orderid,$order_status,$order_notes,$storeid);
				//echo $str = $this->$method($username,$password,$orderid,$order_status,$order_notes);
				echo $str = $this->$method($username,$password,$orderid,$current_order_status,$order_status,$order_notes,$storeid=1);
				break;

				case 'UpdateOrdersShippingStatus':
				echo $str = $this->$method($username,$password,$data,$emailAlert='N',$statustype,$storeid,$others);
				break;

				case 'UpdateOrdersStatusAcknowledge':
				echo $str = $this->$method($username,$password,$data,$statustype,$others) ;
				break;

				case 'addProduct':
				echo $str = $this->$method($username,$password,$data,$storeid=1,$others) ;
				//echo $str = $this->$method($username,$password,$data,$others) ;
				break;

				# NOT BASE
				case 'getItemsByName':
				echo $str =$this->$method($username,$password,$start_item_no,$limit,$itemname ,$storeid,$others);
				break;
				
				case 'getPriceQtyBySku':
				echo $str =$this->$method($username,$password,$limit,$storeid=1,$items);
				break;

				case 'getItemsQuantity':
				echo $str = $this->$method($username,$password,$itemid,$storeid,$others);
				break;

				case 'getCustomers':
				//echo $str = $this->$method($username,$password,$datefrom,$customerid,$limit,$storeid,$others);
				echo $str = $this->$method($username,$password,$datefrom,$customerid,$limit,$storeid=1,$others);
				break;

				case 'getVisibilityStatus':
				echo $str = $method($username,$password,$storeid,$others);
				break;
				
				######### New added cases ###########
				
				case 'getStoreCustomerByIdForEcc':
				echo $str = $this->$method($username,$password,$datefrom,$customerid,$limit,$storeid=1,$others);
				break;
				
				case 'getStoreItemByIdForEcc':
				echo $str =$this->$method($username,$password,$datefrom,$start,$limit,$storeid=1);
				break;
				
				case 'addItemImage':
				echo $str =$this->$method($username,$password,$itemid,$image,$storeid=1);
				break;
				
				case 'getStoreOrderByIdForEcc':
				echo $str = $this->$method($username,$password,$datefrom,$start_order_no,$ecc_excl_list,$order_per_response,$storeid,$others);
				break;
				
				case 'runApns':
				echo $str =$this->$method($username,$password);
				break;
				
				case 'deleteStore':
				echo $str =$this->$method($username,$password,$devicetokenid);
				break;
				
				######################################
			}
		}
	}

	function getRequestData($s1) {
		//return $s1;
		$cipher_alg = MCRYPT_RIJNDAEL_128;
		$key = "d994e5503a58e025";
		$hexiv="d994e5503a58e02525a8fc5eef45223e";
		return $enc_string = @mcrypt_decrypt($cipher_alg, $key, base64_decode($s1), MCRYPT_MODE_CBC, '');
	}
	function response($responseArray) {
		$str = json_encode($responseArray);
		$str = substr($str,strpos($str, "{"),strrpos($str, "}")+1);
		return $str;
	}
	
	 function stringToHex($str) {
		$hex="";
		$zeros = "";
		$len = 2 * strlen($str);
		for ($i = 0; $i < strlen($str); $i++){
			$val = dechex(ord($str{$i}));
			if( strlen($val)< 2 ) $val="0".$val;
			$hex.=$val;
		}
		for ($i = 0; $i < $len - strlen($hex); $i++){
			$zeros .= '0';
		}
		return $hex.$zeros;
	}

	 function hexToString($hex) {
		$str="";
		for($i=0; $i<strlen($hex); $i=$i+2 ) {
			$temp = hexdec(substr($hex, $i, 2));
			if (!$temp) continue;
			$str .= chr($temp);
		}
		return $str;
	}
	
	function writeTestFile($stringData) {
		$myFile = date("m-d-y")."test_file.txt";
		$fh = fopen($myFile, 'a') or die("can't open file");
		fwrite($fh, $stringData);
		fclose($fh);

	}
	
}

class WgBaseResponse extends WgCommon
{
	private $responseArray = array();
	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}
	public function getBaseResponse()
	{
		return $this->responseArray;
	}
}
/*class CompanyInfo
{
	private $responseArray = array();
	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setStoreID($StoreID)
	{
		$this->responseArray['StoreID'] =$StoreID ? $StoreID :"" ;
	}
	public function setStoreName($StoreName)
	{
		$this->responseArray['StoreName'] =$StoreName?$StoreName:"";
	}

	public function setAddress($Address)
	{
		$this->responseArray['Address'] =$Address ? $Address :"";
	}

	public function setcity($city)
	{
		$this->responseArray['city'] = $city ? $city :"";
	}

	public function setState($State)
	{
		$this->responseArray['State'] =$State ? $State : "";
	}

	public function setCountry($Country)
	{
		$this->responseArray['Country'] = $Country ? $Country : "";
	}

	public function setZipcode($Zipcode)
	{
		$this->responseArray['Zipcode'] = $Zipcode ? $Zipcode : "";
	}

	public function setPhone($Phone)
	{
		$this->responseArray['Phone'] =$Phone ? $Phone :"";
	}

	public function setFax($Fax)
	{
		$this->responseArray['Fax'] =$Fax ? $Fax : "";
	}
	
	public function setEmail($email)
	{
		$this->responseArray['Email'] =$Email ? $Email : "";
	}

	public function setWebsite($Website)
	{
		$this->responseArray['Website'] =$Website ? $Website : "";
	}

	public function getCompanyInfo()
	{
		return $this->responseArray;
	}

}*/


class WG_CompanyInfo
{
	private $responseArray	=	array();
	private $company		=	array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setCompany($company)
	{
		$this->company = $company;
	}

	public function getCompanyInfo()
	{
		$this->responseArray['Company'] = $this->company;
		return $this->responseArray;
	}


}

class WG_Company
{
	private $Company = array();

	public function setStoreID($StoreID)
	{
		$this->responseArray['StoreID'] =$StoreID ? $StoreID :"" ;
	}
	public function setStoreName($StoreName)
	{
		$this->responseArray['StoreName'] =$StoreName?$StoreName:"";
	}

	public function setAddress($Address)
	{
		$this->responseArray['Address'] =$Address ? $Address :"";
	}

	public function setcity($city)
	{
		$this->responseArray['city'] = $city ? $city :"";
	}

	public function setState($State)
	{
		$this->responseArray['State'] =$State ? $State : "";
	}

	public function setCountry($Country)
	{
		$this->responseArray['Country'] = $Country ? $Country : "";
	}

	public function setZipcode($Zipcode)
	{
		$this->responseArray['Zipcode'] = $Zipcode ? $Zipcode : "";
	}

	public function setPhone($Phone)
	{
		$this->responseArray['Phone'] =$Phone ? $Phone :"";
	}

	public function setFax($Fax)
	{
		$this->responseArray['Fax'] =$Fax ? $Fax : "";
	}
	
	public function setEmail($email)
	{
		$this->responseArray['Email'] =$Email ? $Email : "";
	}

	public function setWebsite($Website)
	{
		$this->responseArray['Website'] =$Website ? $Website : "";
	}

	public function getCompany()
	{
		return $this->responseArray;
	}

}


class WG_StoresInfo
{
	private $responseArray = array();

	private $stores = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setstores($Stores)
	{
		$this->stores['1'] = $Stores;
	}

	public function getStoresInfo()
	{

		$this->responseArray['Stores'] = $this->stores;
		return $this->responseArray;
		//return '{"StatusCode":"0","StatusMessage":"All Ok","Stores":{"0":{"StoreID":"1","StoreName":"Xcart Store","StoreWebsiteId":"1","StoreWebsiteName":"Xcart website","StoreRootCategoryId":"1","StoreDefaultStoreId":"1"}}}';
	}


}

class WG_Store
{
	private $Store = array();

	public function setStoreID($StoreID)
	{
		$this->Store['StoreID'] = $StoreID ? $StoreID :"";
	}
	public function setStoreName($StoreName)
	{
		$this->Store['StoreName'] = $StoreName ? $StoreName : "";
	}
	public function setStoreType($StoreType)
	{
		$this->Store['StoreType'] = $StoreType ? $StoreType : "";
	}
	public function setStoreWebsiteId($StoreWebsiteId)
	{
		$this->Store['StoreWebsiteId'] = $StoreWebsiteId ? $StoreWebsiteId : "";
	}
	public function setStoreWebsiteName($StoreWebsiteName)
	{
		$this->Store['StoreWebsiteName'] = $StoreWebsiteName ? $StoreWebsiteName : "";
	}
	public function setStoreRootCategoryId($StoreRootCategoryId)
	{
		$this->Store['StoreRootCategoryId'] = $StoreRootCategoryId ? $StoreRootCategoryId : "";
	}
	public function setStoreDefaultStoreId($StoreDefaultStoreId)
	{
		$this->Store['StoreDefaultStoreId'] = $StoreDefaultStoreId ? $StoreDefaultStoreId : "";
	}
	public function getStore()
	{
		return $this->Store;
	}

}

class WG_PaymentMethods
{
	private $responseArray = array();
	private $paymentMethods = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setPaymentMethods($paymentMethods)
	{
		$this->paymentMethods[] = $paymentMethods;
	}

	public function getPaymentMethods()
	{
		$this->responseArray['PaymentMethods'] = $this->paymentMethods;
		return $this->responseArray;
	}

}

class WG_PaymentMethod
{
	private $paymentMethod = array();


	public function setMethodId($MethodId)
	{
		$this->paymentMethod['MethodId'] =$MethodId;
	}
	public function setMethod($Method)
	{
		$this->paymentMethod['Method'] =$Method;
	}
	public function setDetail($Detail)
	{
		$this->paymentMethod['Detail'] =$Detail;
	}

	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

}

class WG_ShippingMethods
{
	private $responseArray = array();
	private $shippingMethods = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setShippingMethods($shippingMethods)
	{
		$this->shippingMethods[] = $shippingMethods;
	}

	public function getShippingMethods()
	{
		$this->responseArray['ShippingMethods'] = $this->shippingMethods;
		return $this->responseArray;
	}

}

class WG_ShippingMethod
{
	private $ShippingMethod = array();

	public function setCarrier($Carrier)
	{
		$this->ShippingMethod['Carrier'] = $Carrier;
	}
	public function setMethods($Methods)
	{
		$this->ShippingMethod['Methods'][] = $Methods;
	}

	public function getShippingMethod()
	{
		return $this->ShippingMethod;
	}

}

class WG_Categories
{
	private $responseArray = array();
	private $Categories = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setCategories($Category)
	{
		$this->Categories[] = $Category;
	}

	public function getCategories()
	{
		$this->responseArray['Categories'] = $this->Categories;
		return $this->responseArray;
	}

}

class WG_Category
{
	private $Category = array();

	/*public function setCategoryID($CategoryID)
	{
		$this->Category['CategoryID'] = $CategoryID ? $CategoryID :"";
	}
	public function setCategoryName($CategoryName)
	{
		$this->Category['CategoryName'] = $CategoryName ? $CategoryName :"";
	}*/
	
	public function setCategoryId($CategoryID)
	{
		$this->Category['CategoryId'] = $CategoryID ? $CategoryID :"";
	}
	public function setCategory($CategoryName)
	{
		$this->Category['Category'] = $CategoryName ? $CategoryName :"";
	}
	
	 
	public function setParentID($ParentID)
	{
		$this->Category['ParentID'] = $ParentID ? $ParentID : "";
	}

	public function getCategory()
	{
		return $this->Category;
	}

}

class WG_Taxes
{
	private $responseArray = array();
	private $Taxes = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setTaxes($Tax)
	{
		$this->Taxes[] = $Tax;
	}

	public function getTaxes()
	{
		$this->responseArray['Taxes'] = $this->Taxes;
		return $this->responseArray;
	}
}

class WG_Tax
{
	private $Tax = array();

	public function setTaxID($TaxID)
	{
		$this->Tax['TaxID'] = $TaxID ? $TaxID :"";
	}

	public function setTaxName($TaxName)
	{
		$this->Tax['TaxName'] = $TaxName ? $TaxName :"";
	}

	public function getTax()
	{
		return $this->Tax;
	}
}

class WG_Manufacturers
{
	private $responseArray = array();
	private $Manufacturers = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setManufacturers($Manufacturer)
	{
		$this->Manufacturers[] = $Manufacturer;
	}

	public function getManufacturers()
	{
		$this->responseArray['Manufacturers'] = $this->Manufacturers;
		return $this->responseArray;
	}
}

class WG_Manufacturer
{
	private $Manufacturer = array();

	public function setManufacturerID($ManufacturerID)
	{
		$this->Manufacturer['ManufacturerID'] = $ManufacturerID ? $ManufacturerID :"";
	}

	public function setManufacturerName($ManufacturerName)
	{
		$this->Manufacturer['ManufacturerName'] = $ManufacturerName ? $ManufacturerName :"";
	}

	public function getManufacturer()
	{
		return $this->Manufacturer;
	}
}


class WG_Attributesets
{
	private $responseArray = array();
	private $Attributesets = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setAttributesets($Attribute)
	{
		$this->Attributesets[] = $Attribute;
	}

	public function getAttributesets()
	{
		$this->responseArray['Attributesets'] = $this->Attributesets;
		return $this->responseArray;
	}
}

class WG_Attribute
{
	private $Attribute = array();

	public function setAttributeID($AttributeID)
	{
		$this->Attribute['AttributeID'] = $AttributeID ? $AttributeID :"";
	}

	public function setAttributeName($AttributeName)
	{
		$this->Attribute['AttributeName'] = $AttributeName ? $AttributeName :"";
	}

	public function getAttribute()
	{
		return $this->Attribute;
	}
}

class WG_OrderStatuses
{
	private $responseArray = array();
	private $OrderStatuses = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setOrderStatuses($OrderStatus)
	{
		$this->OrderStatuses[] = $OrderStatus;
	}

	public function getOrderStatuses()
	{
		$this->responseArray['OrderStatus'] = $this->OrderStatuses;
		return $this->responseArray;
	}
}

class WG_OrderStatus
{
	private $OrderStatus = array();

	public function setOrderStatusID($OrderStatusID)
	{
		$this->OrderStatus['StatusId'] = $OrderStatusID ? $OrderStatusID :"";
	}

	public function setOrderStatusName($OrderStatusName)
	{
		$this->OrderStatus['StatusName'] = $OrderStatusName ? $OrderStatusName :"";
	}

	public function getOrderStatus()
	{
		return $this->OrderStatus;
	}
}



class WG_Items
{
	private $responseArray = array();
	private $Items = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}
	public function setTotalRecordFound($TotalRecordFound)
	{
		$this->responseArray['TotalRecordFound'] =$TotalRecordFound;
	}
	public function setTotalRecordSent($TotalRecordSent)
	{
		$this->responseArray['TotalRecordSent'] =$TotalRecordSent?$TotalRecordSent:"0";
	}
	public function setItems($Items1)
	{

		$this->Items[] = $Items1;
	}

	public function getItems()
	{
		$this->responseArray['Items'] = $this->Items;
		return $this->responseArray;
	}

	public function setItemsTotalRecordSent($Items1)
	{

		$this->Items['Items'][] = $Items1;
	}
	
	public function getItemsTotalRecordSent()
	{
		$this->responseArray['TotalRecordSent'] = $this->Items;
		return $this->responseArray;
	}

	public function setItemImageFlag($ItemImageFlag)
	{
		$this->responseArray['ItemImageFlag'] = $ItemImageFlag;
	}


	public function getItemsNode()
	{
		return $this->responseArray;
	}


}
class WG_Item
{
	private $Item = array();

	public function setItemID($ItemID)
	{
		$this->Item['ItemID'] = $ItemID ? $ItemID :"";
	}
	public function setItemCode($ItemCode)
	{
		$this->Item['ItemCode'] = $ItemCode ? $ItemCode :"";
	}
	public function setItemDescription($ItemDescription)
	{
		
		$ItemDescription	=	preg_replace('/\s+/', ' ',html_entity_decode($ItemDescription));
		$ItemDescription	=	preg_replace("/<.*?>/", "", $ItemDescription);
		$ItemDescription	=	preg_replace("/&#?[a-z0-9]{2,8};/i","",$ItemDescription);
		$this->Item['ItemDescription'] = $ItemDescription ? $ItemDescription : "";
	}
	public function setItemShortDescr($ItemShortDescr)
	{
		/*$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);*/
		
		$ItemShortDescr	=	preg_replace('/\s+/', ' ',$ItemShortDescr);
		$ItemShortDescr	=	preg_replace("/<.*?>/", "", html_entity_decode($ItemShortDescr));
		$ItemShortDescr	=	preg_replace("/&#?[a-z0-9]{2,8};/i","",$ItemShortDescr);
		$this->Item['ItemShortDescr'] = $ItemShortDescr ? $ItemShortDescr : "";
	}
	public function setCategories($Categories)
	{
		$this->Item['Categories'][] = $Categories ? $Categories : "";
	}
	public function setItemImages($ItemImages)
	{
		$this->Item['ItemImages'][] = $ItemImages ? $ItemImages : "";
	}
	public function setManufacturer($Manufacturer)
	{
		$this->Item['manufacturer'] = $Manufacturer ? $Manufacturer : "";
	}
	public function setQuantity($Quantity)
	{
		$this->Item['Quantity'] = $Quantity ? $Quantity : "0";
	}
	public function setUnitPrice($UnitPrice)
	{
		$this->Item['UnitPrice'] = $UnitPrice ? $UnitPrice : "0";
	}
	public function setListPrice($ListPrice)
	{
		$this->Item['ListPrice'] = $ListPrice ? $ListPrice : "0";
	}
	public function setWeight($Weight)
	{
		$this->Item['Weight'] = $Weight ? $Weight : "0";
	}
	public function setLowQtyLimit($LowQtyLimit)
	{
		$this->Item['LowQtyLimit'] = $LowQtyLimit ? $LowQtyLimit : "0";
	}
	public function setFreeShipping($FreeShipping)
	{
		$this->Item['FreeShipping'] = $FreeShipping ? $FreeShipping : "0";
	}
	public function setDiscounted($Discounted)
	{
		$this->Item['Discounted'] = $Discounted ? $Discounted : "0";
	}
	public function setShippingFreight($ShippingFreight)
	{
		$this->Item['ShippingFreight'] = $ShippingFreight ? $ShippingFreight : "0";
	}
	public function setWeight_Symbol($Weight_Symbol)
	{
		$this->Item['Weight_Symbol'] = $Weight_Symbol ? $Weight_Symbol : "0";
	}

	public function setWeight_Symbol_Grams($Weight_Symbol_Grams)
	{
		$this->Item['Weight_Symbol_Grams'] = $Weight_Symbol_Grams ? $Weight_Symbol_Grams : "0";
	}
	public function setTaxExempt($setTaxExempt)
	{
		$this->Item['TaxExempt'] = $setTaxExempt ? $setTaxExempt : "0";
	}

	public function setUpdatedAt($UpdatedAt)
	{
		$this->Item['UpdatedAt'] = $UpdatedAt ? $UpdatedAt : "0";
	}

	public function setImageUrl($ImageUrl)
	{
		$this->Item['ImageUrl'] = $ImageUrl ? $ImageUrl : '';
	}

	public function setItemVariants($ItemVariants)
	{
		if($ItemVariants) {
			$this->Item['ItemVariants'][] = $ItemVariants ? $ItemVariants : '';
		} else {
			$this->Item['ItemVariants'][] = $ItemVariants ? $ItemVariants : '';
		}
	}

	public function setItemOptions($ItemOptions)
	{
		//$this->Item['ItemOptions'][] = $ItemOptions ? $ItemOptions : '';
		
		if($ItemOptions) {
			$this->Item['ItemOptions'][] = $ItemOptions ;
		} else {
			$this->Item['ItemOptions'][] = '';
		}
	}

	#Extra node fro Orders

	public function setShippedQuantity($ShippedQuantity)
	{
		$this->Item['ShippedQuantity'] = $ShippedQuantity ? $ShippedQuantity : "0";
	}
	public function setOneTimeCharge($OneTimeCharge)
	{
		$this->Item['OneTimeCharge'] = $OneTimeCharge ? $OneTimeCharge : "0";
	}
	public function setItemTaxAmount($ItemTaxAmount)
	{
		$this->Item['ItemTaxAmount'] = $ItemTaxAmount ? $ItemTaxAmount : "0";
	}


	#Nodes used for add product
	public function setStatus($Status)
	{
		$this->Item['Status'] = $Status ? $Status : '';
	}
	public function setProductID($ProductID)
	{
		$this->Item['ProductID'] = $ProductID ? $ProductID : '';
	}
	public function setSku($Sku)
	{
		$this->Item['Sku'] = $Sku ? $Sku : '';
	}

	public function setProductName($ProductName)
	{
		$this->Item['ProductName'] = $ProductName ? $ProductName : '';
	}

	#node for sync product

	
	public function setItemUpdateStatus($ItemUpdateStatus)
	{
		$this->Item['ItemUpdateStatus'] = $ItemUpdateStatus ? $ItemUpdateStatus : '';
	}
    public function setPrice($Price)
	{
		$this->Item['Price'] = $Price ? $Price : '';
	}

	public function getItem()
	{

		return $this->Item;
	}
}

class WG_ItemImages
{
	private $responseArray = array();
	private $ItemImages = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}

	public function setItemImages($Image)
	{
		$this->ItemImages[] = $Image;
	}

	public function getItemImages()
	{
		$this->responseArray['ItemImages'] = $this->ItemImages;
		return $this->responseArray;
	}

}


class WG_Itemoption
{

	private $responseArray = array();

	 # Nodes for item options
	public function setOptionID($OptionID)
	{
		$this->responseArray['OptionID'] = $OptionID ? $OptionID : '';
	}
	public function setOptionValue($OptionValue)
	{
		$this->responseArray['OptionValue'] = $OptionValue ? $OptionValue : '';
	}
	public function setOptionName($OptionName)
	{
		$this->responseArray['OptionName'] = $OptionName ? $OptionName : '';
	}
	public function setOptionPrice($OptionPrice)
	{
		$this->responseArray['OptionPrice'] = $OptionPrice ? $OptionPrice : '';
	}
	
	public function setOptionWeight($OptionWeight)
	{
		$this->responseArray['OptionWeight'] = $OptionWeight ? $OptionWeight : '';
	}
	

	public function getItemoption()
	{
		return $this->responseArray;
	}

}

class WG_Image
{
	private $Image = array();

	public function setItemID($ItemID)
	{
		$this->Image['ItemID'] = $ItemID ? $ItemID :"";
	}
	public function setItemImageID($ItemImageID)
	{
		$this->Image['ItemImageID'] = $ItemImageID ? $ItemImageID :"";
	}
	public function setItemImageFileName($ItemImageFileName)
	{
		$this->Image['ItemImageFileName'] = $ItemImageFileName ? $ItemImageFileName : "";
	}
	public function setItemImageUrl($ItemImageUrl)
	{
		$this->Image['ItemImageUrl'] = $ItemImageUrl ? $ItemImageUrl : "";
	}
	public function getImage()
	{
		return $this->Image;
	}

}

class WG_Variants
{
	private $responseArray = array();
	private $ItemVariants = array();
	public function setItemVariants($Variants1)
	{

		$this->ItemVariants[] = $Variants1;
	}

	
	public function getVariants()
	{
		$this->responseArray['ItemVariants'] = $this->ItemVariants;
		return $this->responseArray;
	}
}

class WG_Variant
{

	private $ItemVariant = array();

	
	public function setItemCode($ItemCode)
	{
		$this->ItemVariant['ItemCode'] = $ItemCode ? $ItemCode :"";
	}
	public function setVarientID($VarientID)
	{
		$this->ItemVariant['VarientID'] = $VarientID ? $VarientID :"";
	}
	public function setVariantSku($VarientSku)
	{
		$this->ItemVariant['Sku'] = $VarientSku ? $VarientSku :"";
	}
	public function setQuantity($Quantity)
	{
		$this->ItemVariant['Quantity'] = $Quantity ? $Quantity : "";
	}
	public function setUnitPrice($UnitPrice)
	{
		$this->ItemVariant['UnitPrice'] = $UnitPrice ? $UnitPrice : "";
	}
	public function setWeight($Weight)
	{
		$this->ItemVariant['Weight'] = $Weight ? $Weight : "";
	}
	public function setStatus($Status)
    {
        $this->ItemVariant['Status'] = $Status ? $Status : "";
    }
	public function getVariant()
	{

		return $this->ItemVariant;
	}

}
class WG_Options
{
	private $responseArray = array();
	private $ItemOptions = array();
	public function setItemOptions($Options1)
	{

		$this->ItemOptions[] = $Options1 ? $Options1 : '';
	}

	
	public function getOptions()
	{
		$this->responseArray['ItemOptions'] = $this->ItemOptions;
		return $this->responseArray;
	}
}

class WG_Option
{


}


class WG_Orders
{

	private $responseArray = array();
	private $Orders = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] = $StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}
	public function setTotalRecordFound($TotalRecordFound)
	{
		$this->responseArray['TotalRecordFound'] =$TotalRecordFound;
	}
	public function setTotalRecordSent($TotalRecordSent)
	{
		$this->responseArray['TotalRecordSent'] =$TotalRecordSent?$TotalRecordSent:"0";
	}

	public function setOrders($Order)
	{
		$this->Orders[] = $Order;
	}

	public function getOrders()
	{
		$this->responseArray['Orders'] = $this->Orders;
		return $this->responseArray;
	}

	public function getOrderResponse()
	{
		//$this->responseArray['Orders'] = $this->Orders;
		return $this->responseArray;
	}
}
class WG_Order
{
	private $responseArray = array();
	private $Order = array();

	public function setOrderId($OrderId)
	{
		$this->Order['OrderId'] =$OrderId;
	}

	public function setTitle($Title)
	{
		$this->Order['Title'] =$Title;
	}

	public function setFirstName($FirstName)
	{
		$this->Order['FirstName'] =$FirstName;
	}

	public function setLastName($LastName)
	{
		$this->Order['LastName'] =$LastName;
	}

	public function setDate($Date)
	{
		$this->Order['Date'] =$Date;
	}

	public function setTime($Time)
	{
		$this->Order['Time'] =$Time;
	}

	public function setStoreID($StoreID)
	{
		$this->Order['StoreID'] =$StoreID;
	}
	public function setStoreName($StoreName)
	{
		$this->Order['StoreName'] =$StoreName;
	}
	public function setCurrency($Currency)
	{
		$this->Order['Currency'] =$Currency;
	}
	public function setWeight_Symbol($Weight_Symbol)
	{
		$this->Order['Weight_Symbol'] =$Weight_Symbol;
	}
	public function setWeight_Symbol_Grams($Weight_Symbol_Grams)
	{
		$this->Order['Weight_Symbol_Grams'] =$Weight_Symbol_Grams;
	}

	public function setCustomerId($CustomerId)
	{
		$this->Order['CustomerId'] =$CustomerId;
	}
	public function setComment($Comment)
	{
		$this->Order['Comment'] =$Comment;
	}
	public function setStatus($Status)
	{
		$this->Order['Status'] =$Status;
	}
	public function setNotes($Notes)
	{
		$this->Order['Notes'] =$Notes;
	}
	public function setFax($Fax)
	{
		$this->Order['Fax'] =$Fax;
	}
	public function setShippedOn($ShippedOn)
	{
		$this->Order['ShippedOn'] =$ShippedOn;
	}
	public function setShippedVia($ShippedVia)
	{
		$this->Order['ShippedVia'] =$ShippedVia;
	}
	
	public function setOrderInfo($order) {
		$this->Order['OrderInfo'][] = $order;
	}
	
	public function setOrderItems($OrderItems)
	{
		$this->Order['Items'][] = $OrderItems;
	}
	public function setOrderBillInfo($Bill)
	{
		$this->Order['Bill'][] = $Bill;
	}
	public function setOrderShipInfo($Ship)
	{
		$this->Order['Ship'][] = $Ship;
	}
	public function setOrderChargeInfo($Charges)
	{
		$this->Order['Charges'][] = $Charges;
	}
	public function getOrder()
	{
		return $this->Order;
	}

}

class WG_OrderInfo
{
	private $responseArray = array();

	public function setOrderId($OrderId)
	{
		$this->responseArray['OrderId'] =$OrderId;
	}

	public function setTitle($Title)
	{
		$this->responseArray['Title'] =$Title;
	}

	public function setFirstName($FirstName)
	{
		$this->responseArray['FirstName'] =$FirstName;
	}

	public function setLastName($LastName)
	{
		$this->responseArray['LastName'] =$LastName;
	}

	public function setDate($Date)
	{
		$this->responseArray['Date'] =$Date;
	}

	public function setTime($Time)
	{
		$this->responseArray['Time'] =$Time;
	}

	public function setStoreID($StoreID)
	{
		$this->responseArray['StoreID'] =$StoreID;
	}
	public function setStoreName($StoreName)
	{
		$this->responseArray['StoreName'] =$StoreName;
	}
	public function setCurrency($Currency)
	{
		$this->responseArray['Currency'] =$Currency;
	}
	public function setWeight_Symbol($Weight_Symbol)
	{
		$this->responseArray['Weight_Symbol'] =$Weight_Symbol;
	}
	public function setWeight_Symbol_Grams($Weight_Symbol_Grams)
	{
		$this->responseArray['Weight_Symbol_Grams'] =$Weight_Symbol_Grams;
	}

	public function setCustomerId($CustomerId)
	{
		$this->responseArray['CustomerId'] =$CustomerId;
	}
	public function setComment($Comment)
	{
		$this->responseArray['Comment'] =$Comment;
	}
	public function setStatus($Status)
	{
		$this->responseArray['Status'] =$Status;
	}
	public function setNotes($Notes)
	{
		$this->responseArray['Notes'] =$Notes;
	}
	public function setFax($Fax)
	{
		$this->responseArray['Fax'] =$Fax;
	}
	public function getOrderInfo()
	{
		return $this->responseArray;
	}
}
class WG_CreditCard
{

	private $responseArray = array();

	public function setCreditCardType($CreditCardType)
	{
		$this->responseArray['CreditCardType'] =$CreditCardType;
	}
		public function setCreditCardCharge($CreditCardCharge)
	{
		$this->responseArray['CreditCardCharge'] =$CreditCardCharge;
	}
		public function setExpirationDate($ExpirationDate)
	{
		$this->responseArray['ExpirationDate'] =$ExpirationDate;
	}
		public function setCreditCardName($CreditCardName)
	{
		$this->responseArray['CreditCardName'] =$CreditCardName;
	}
		public function setCreditCardNumber($CreditCardNumber)
	{
		$this->responseArray['CreditCardNumber'] =$CreditCardNumber;
	}
		public function setCVV2($CVV2)
	{
		$this->responseArray['CVV2'] =$CVV2;
	}
		public function setAdvanceInfo($AdvanceInfo)
	{
		$this->responseArray['AdvanceInfo'] =$AdvanceInfo;
	}
		public function setTransactionId($TransactionId)
	{
		$this->responseArray['TransactionId'] =$TransactionId;
	}
		public function getCreditCard()
	{
		return $this->responseArray;
	}
}
class WG_Bill
{

	private $responseArray = array();

	public function setCreditCardInfo($CreditCard)
	{
		$this->responseArray['CreditCard'] =$CreditCard;
	}

	public function setPayMethod($PayMethod)
	{
		$this->responseArray['PayMethod'] =$PayMethod;
	}
	public function setPayStatus($PayStatus)
	{
		$this->responseArray['PayStatus'] =$PayStatus;
	}
	public function setTitle($Title)
	{
		$this->responseArray['Title'] =$Title;
	}
	public function setFirstName($FirstName)
	{
		$this->responseArray['FirstName'] =$FirstName;
	}
	public function setLastName($LastName)
	{
		$this->responseArray['LastName'] =$LastName;
	}
	public function setCompanyName($CompanyName)
	{
		$this->responseArray['CompanyName'] =$CompanyName;
	}


	public function setAddress1($Address1)
	{
		$this->responseArray['Address1'] =$Address1;
	}
	public function setAddress2($Address2)
	{
		$this->responseArray['Address2'] =$Address2?$Address2:NULL;
	}
	public function setCity($City)
	{
		$this->responseArray['City'] =$City;
	}
	public function setState($State)
	{
		$this->responseArray['State'] =$State;
	}
	public function setZip($Zip)
	{
		$this->responseArray['Zip'] =$Zip;
	}
	public function setCountry($Country)
	{
		$this->responseArray['Country'] =$Country;
	}
	public function setEmail($Email)
	{
		$this->responseArray['Email'] =$Email;
	}
	public function setPhone($Phone)
	{
		$this->responseArray['Phone'] =$Phone;
	}
	public function setPONumber($PONumber)
	{
		$this->responseArray['PONumber'] =$PONumber;
	}
	public function getBill()
	{

		return $this->responseArray;
	}
}

class WG_Ship
{
	private $responseArray = array();

	public function setShipMethod($ShipMethod)
	{
		$this->responseArray['ShipMethod'] =$ShipMethod;
	}
	public function setCarrier($Carrier)
	{
		$this->responseArray['Carrier'] =$Carrier;
	}
	public function setTrackingNumber($TrackingNumber)
	{
		$this->responseArray['TrackingNumber'] =$TrackingNumber;
	}
	public function setTitle($Title)
	{
		$this->responseArray['Title'] =$Title;
	}
	public function setFirstName($FirstName)
	{
		$this->responseArray['FirstName'] =$FirstName;
	}
	public function setLastName($LastName)
	{
		$this->responseArray['LastName'] =$LastName;
	}
	public function setCompanyName($CompanyName)
	{
		$this->responseArray['CompanyName'] =$CompanyName;
	}
	public function setAddress1($Address1)
	{
		$this->responseArray['Address1'] =$Address1;
	}
	public function setAddress2($Address2)
	{
		$this->responseArray['Address2'] =$Address2?$Address2:NULL;
	}
	public function setCity($City)
	{
		$this->responseArray['City'] =$City;
	}
	public function setState($State)
	{
		$this->responseArray['State'] =$State;
	}
	public function setZip($Zip)
	{
		$this->responseArray['Zip'] =$Zip;
	}
	public function setCountry($Country)
	{
		$this->responseArray['Country'] =$Country;
	}
	public function setEmail($Email)
	{
		$this->responseArray['Email'] =$Email;
	}

	public function setPhone($Phone)
	{
		$this->responseArray['Phone'] =$Phone;
	}
	public function setDiscount($Discount)
	{
		$this->responseArray['Discount'] =$Discount;
	}
	public function setStoreCredit($StoreCredit)
	{
		$this->responseArray['StoreCredit'] =$StoreCredit;
	}
	public function setTax($Tax)
	{
		$this->responseArray['Tax'] =$Tax;
	}
	public function setShipping($Shipping)
	{
		$this->responseArray['Shipping'] =$Shipping;
	}
	public function setTotal($Total)
	{
		$this->responseArray['Total'] =$Total;
	}

	public function getShip()
	{
		return $this->responseArray;
	}
}

class WG_Charges
{

	private $responseArray = array();

	public function setDiscount($Discount)
	{
		$this->responseArray['Discount'] =$Discount;
	}
	public function setStoreCredit($StoreCredit)
	{
		$this->responseArray['StoreCredit'] =$StoreCredit;
	}
	public function setTax($Tax)
	{
		$this->responseArray['Tax'] =$Tax;
	}
	public function setShipping($Shipping)
	{
		$this->responseArray['Shipping'] =$Shipping;
	}
	public function setTotal($Total)
	{
		$this->responseArray['Total'] =$Total;
	}
	public function getCharges()
	{
		return $this->responseArray;
	}
}

class WG_Customers
{
	private $responseArray = array();

	public function setStatusCode($StatusCode)
	{
		$this->responseArray['StatusCode'] =$StatusCode;
	}
	public function setStatusMessage($StatusMessage)
	{
		$this->responseArray['StatusMessage'] =$StatusMessage;
	}
	public function setTotalRecordFound($TotalRecordFound)
	{
		$this->responseArray['TotalRecordFound'] =$TotalRecordFound;
	}
	public function setTotalRecordSent($TotalRecordSent)
	{
		$this->responseArray['TotalRecordSent'] = $TotalRecordSent?$TotalRecordSent:"0";
	}
	public function setCustomer($Customer)
	{
		$this->responseArray['Customers'][] = $Customer;
	}
	public function getCustomers()
	{
		return $this->responseArray;
	}
}

class WG_Customer
{
	private $responseArray = array();
	public function setCustomerId($CustomerId)
	{
		$this->responseArray['CustomerId'] =$CustomerId;
	}
	public function setFirstName($FirstName)
	{
		$this->responseArray['FirstName'] =$FirstName;
	}
	public function setMiddleName($MiddleName)
	{
		$this->responseArray['MiddleName'] =$MiddleName;
	}
	public function setLastName($LastName)
	{
		$this->responseArray['LastName'] =$LastName;
	}
	public function setCustomerGroup($CustomerGroup)
	{
		$this->responseArray['CustomerGroup'] =$CustomerGroup;
	}
	public function setemail($email)
	{
		$this->responseArray['email'] =$email;
	}
	public function setAddress1($Address1)
	{
		$this->responseArray['Address1'] =$Address1;
	}
	public function setAddress2($Address2)
	{
		$this->responseArray['Address2'] =$Address2;
	}
	public function setCity($City)
	{
		$this->responseArray['City'] =$City;
	}
	public function setState($State)
	{
		$this->responseArray['State'] =$State;
	}
	public function setZip($Zip)
	{
		$this->responseArray['Zip'] =$Zip;
	}
	public function setCountry($Country)
	{
		$this->responseArray['Country'] =$Country;
	}
	public function setPhone($Phone)
	{
		$this->responseArray['Phone'] =$Phone;
	}public function setCreatedAt($CreatedAt)
	{
		$this->responseArray['CreatedAt'] =$CreatedAt;
	}public function setUpdatedAt($UpdatedAt)
	{
		$this->responseArray['UpdatedAt'] =$UpdatedAt;
	}public function setLifeTimeSale($LifeTimeSale)
	{
		$this->responseArray['SalesStatistics']['LifeTimeSale'] =$LifeTimeSale;
	}public function setAverageSale($AverageSale)
	{
		$this->responseArray['SalesStatistics']['AverageSale'] =$AverageSale;
	}

	public function getCustomer()
	{
		return $this->responseArray;
	}
}
?>