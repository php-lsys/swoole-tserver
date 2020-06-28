namespace php LSM
//以下定义仅在内置中间件中使用到
//不使用内置中间件可忽略以下定义
/**
 * Token异常
 */
exception TokenException{
	/**
	 * 状态
	 */
	1:required i16 status
	/**
	 * 消息
	 */
  	2:optional string message
  	/**
	 * 文件
	 */
  	3:required string file
  	/**
  	 * 行号
  	 */
  	4:required string line
}
/**
 * 验证秘钥
 */
struct TokenParam{
  /**
   * 版本
   */
  1:required string version
  /**
   * 秘钥
   */ 
  2:required string signature
  /**
   * 时间戳
   */
  3:required string timestamps
  /**
   * 秘钥
   */ 
  4:required string platform
  /**
   * 平台
   */
  5:required string ip
  /**
   * 请求标识
   */
  6:optional string unique
}
/**
 * 拦截异常
 */
exception BreakerException{
	/**
	 * 状态
	 */
	1:required i16 status
	/**
	 * 消息
	 */
  	2:optional string message
  	/**
	 * 限制类型[ip或request]
	 */
	3:required string type
  	/**
	 * 下次可请求时间
	 */
  	4:required i16 time 
  	/**
	 * 文件
	 */
  	5:required string file
  	/**
  	 * 行号
  	 */
  	6:required string line
}
service LSMService 
{
    /**
     * 清理请求限制
     */
	void breakerClearRequestLimit(1:TokenParam token,2:string method='')throws(1:TokenException tokenerr);
	/**
     * 清理IP限制
     */
	void breakerClearIpLimit(1:TokenParam token,2:string method='')throws(1:TokenException tokenerr);
}