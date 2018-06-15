{__NOLAYOUT__}
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>跳转提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
        html,body{
          height: 100%;
          overflow: hidden;
          background: #f5f5f5;
        }
        .warrp{
          width: 100%;
          height: 100%;
          max-width: 640px;
          text-align: center;
          position: relative;
        }
        .warrp .tips{
          width: 60%;
          line-height: 28px;
          padding: 10px 5px;
          position: absolute;
          left: 50%;
          top: 50%;
          transform: translate(-50%,-50%);
          color: #ffffff;
          font-size: 14px;
          border-radius: 5px;
          background: rgba(0,0,0,0.56)
        }
        #time{
          margin-left: 8px;
        }
    </style>
</head>
<body>
  <div class="warrp">
    <div class="tips">
      <p><?php echo(strip_tags($msg));?><span id="time"><?php echo($wait);?></span></p>
    </div>
  </div>
    <!-- <div class="system-message">
        <?php switch ($code) {?>
            <?php case 1:?>
            <p class="success"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
            <?php case 0:?>
            <h1>:(</h1>
            <p class="error"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
        <?php } ?>
        <p class="detail"></p>
        <p class="jump">
            页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
        </p>
    </div> -->
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('time'),
                href = '<?php echo($url);?>';
            if(wait == 0) {
              location.href = href;
              return false;
            }
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>
</html>
