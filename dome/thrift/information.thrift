namespace php Information

include "../../src/thrift/lsm.thrift"


struct DomeResultPage{
  1:optional i32 Bpage=1
  2:optional i32 BpageCount=0
  3:optional i32 Bcount=0
}

service DomeProduct extends lsm.LSMService
{
 	/**
    * 方法注释
    */
	DomeResultPage test(1:string msg,2:lsm.TokenParam token)throws(1:lsm.TokenException toerr,2:lsm.BreakerException brerr);
}