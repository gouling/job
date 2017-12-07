<?php
    require 'lib/index.php';

    $client = new \HBase\Client('127.0.0.1', 9090);

    $client->addRow([
        'table' => 'news',
        'key' => 4,
        'data' => [
            'title:' => '个人信息倒卖：内鬼监守自盗和黑客攻击是主要渠道',
            'content:' => '新华社福州12月6日电题：“内鬼”是泄露主要渠道“鲜活信息”单条能卖数十元——谁在倒卖我们的个人信息？

新华社“新华视点”记者郑良、孙飞

去年以来，在公安部部署下，全国各地开展打击整治网络侵犯公民个人信息犯罪专项行动，取得了明显成效。但当前侵犯公民信息安全违法犯罪活动仍时有发生，非法收集、贩卖公民个人信息手法多样，手段更为隐蔽。

“新华视点”记者在多地反诈骗中心了解到，目前电信网络诈骗案件90％以上是违法分子靠掌握公民详细信息进行的精准诈骗，从已破获案件看，“内鬼”监守自盗和黑客攻击仍是公民个人信息泄露的主要渠道。

有犯罪分子专门收集老人信息设置骗局“鲜活信息”单条能卖数十元

12月以来，福建厦门连续发生7起老年人被骗警情，单笔被骗最高金额达13万元。诈骗分子冒充公检法办案人员，以受害者涉嫌洗钱、身份被冒用等为由，骗老人到银行转账汇款至安全账户。

厦门市公安局反诈骗中心负责人告诉记者，诈骗人员通过收集老人信息，对老人的姓名、电话甚至家庭情况掌握得一清二楚，精心设计骗局。

厦门市公安局刑侦支队副支队长吴世勇说，非法获取、买卖公民个人信息已形成黑色产业链。一方面，大数据营销方兴未艾，各类广告公司、大数据运营商、保险公司、中介公司等机构对于公民个人信息存在庞大需求；另一方面，侵犯公民个人信息犯罪为电信网络诈骗、金融诈骗、敲诈勒索等提供了作案便利，这是此类违法犯罪活动屡禁不绝的重要原因。

据记者了解，在公安机关严厉打击下，网站、论坛、QQ群公开叫卖、求购公民个人信息大幅减少，现在更多表现为买卖方单线联系、熟人介绍，在小规模的同业QQ群、微信群用隐晦的关键词交流买卖信息。

据深圳警方抓获的犯罪嫌疑人谢某交代，他从网上搜索到售卖信息资源的QQ号，联系上后，对方提出要求，购买7万多条信息资源需先付600元定金，资源到手后再付款700元。“付款完成后，对方就把我删除了。”谢某说。

更令人吃惊的是，根据买方的需求，在互联网上能精准买到相应的个人信息。福州福清市法院判决的一起绑架案中，两名犯罪人员为谋财寻找作案目标，在福清市区发现受害者驾驶一辆豪华轿车，通过车牌号在网上求购到车主信息，准确掌握了受害者及其家庭人员信息，并策划实施了绑架，因受害者激烈反抗未成功。

据记者调查，根据信息质量和倒卖的层级，从几分钱一条到几百元一条，价格不等。“含金量高”的个人信息价格较高，新开楼盘业主、新购车辆车主、新生儿、入学新生、新近下单的网络购物订单等“鲜活”信息，单条能卖到十几元乃至数十元。

“内鬼”是信息泄露主要渠道黑客攻击窃取信息呈增长趋势

福建公安机关相关负责人告诉记者，从破获案件看，“内鬼”监守自盗和黑客攻击仍然是公民个人信息泄露的主要渠道。

福州公安机关近日破获一起特大侵犯公民个人信息案，查获公民个人房产、征信报告、车辆、联系方式等信息超过千万条，抓获的19名犯罪嫌疑人绝大多数是房产开发、销售、中介等内部人员。他们利用职务便利，非法收集、交换、出售公民个人信息，从中牟利。

从公安机关破获和法院判决的案例看，车辆、征信报告、银行账户、房产、教育、医疗等信息成为“抢手货”，相关部门内部人员监守自盗案件时有发生。

在深圳福田的某资产管理有限公司，警方缴获非法公民个人信息1万余条、非法个人银行征信报告1000余份。警方最终摸查出非法提供这些个人信息的是3名某银行深圳分行个贷部在职员工。

此外，黑客攻击窃取个人信息呈增长趋势。“网络安全形势十分严峻。”从事网络安全保护业务的厦门服云信息科技有限公司技术人员朱一帆告诉记者，“从对政府机构、大型国企、高校、电商、交通等重点客户遭遇互联网黑客攻击的实时监测数据看，网络黑客入侵重点网站窃取信息有增无减，攻击手段日益多样化，而大量掌握公民个人信息的一些机构网络安全防护意识不强，投入不足，特别是没有对不断出现的网络安全漏洞及时采取修复措施，很容易被黑客攻陷，造成大规模信息泄露。”

筑牢源头“防护墙”重拳整治“买方市场”

公安、网络安全等部门人士提出，保护公民个人信息安全，筑牢源头“防护墙”是关键，要建立有效的事前预防、实时监测、主动预警机制。

厦门市公安局刑侦支队民警陈鸿说：“调查发现，一些掌握大量公民个人信息的单位在防范信息泄露方面缺乏有效机制，对内部人员缺乏监督、制约，有的工作人员随意浏览、下载相关内部信息，而单位长期不知情，直至发生大规模泄露严重后果。”

陈鸿等人建议，应在大量掌握公民个人重要信息的部门，如银行、房产中心、税务、车管等部门建立数字证书制度，工作人员必须使用专属于个人的数字证书才能登录、查看、下载单位信息系统数据，实现全程留痕，一旦发生信息泄露，可以倒查责任人员。

不少办案民警提出，目前我国对收集、购买公民个人信息的买方市场打击力度明显不够。对信息买方市场的整治应当加强。

今年5月，最高人民法院、最高人民检察院发布《关于办理侵犯公民个人信息刑事案件适用法律若干问题的解释》，规定利用非法购买、收受的公民个人信息获利5万元以上等情形的；非法获取、出售或者提供行踪轨迹信息、通信内容、征信信息、财产信息50条以上等情形的，应当认定为“情节严重”，面临承担刑事责任的法律后果。

不少公安、法院人士提出，这一司法解释出台后，长期困扰司法机关的法律适用难题得到缓解，多部门共同打击的合力正在形成。',
            'readTotal:' => '2018'
        ]
    ]);
    $client->delRow('news', 4);

    print_r($client->getTableNames());
    print_r($client->getRows('news'));
    print_r($client->getRows('users'));