<?php
/**
 * 2017 Jose Luis Estevez Prieto
 * GNU General Public License v3.0
 * 
 */
?>
<!DOCTYPE html>
<html>
    <head lang="en">
        <meta charset="utf-8">
        <title>Generar HTML de una colección Postman V2</title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta content="no-cache" http-equiv="cache-control">
        <meta content="0" http-equiv="expires">
        <meta name="author" content="jestevez" />
        <!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame
        Remove this if you use the .htaccess -->
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>

        <!-- Bootstrap -->
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />

        <link href="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/styles/darkula.min.css" rel="stylesheet">
        <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.2.0/highlight.min.js"></script>
        <script>hljs.initHighlightingOnLoad();</script>
        <!-- Font Awesome -->
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">

        <style rel="stylesheet">

            .nav-tree .branch-header ~ li{ padding-left: 15px; }

            .nav-tree .trunk li.stem{ display:none; }

            .nav-tree .branch ul{ margin-left: 3px; }

        </style>

        <style>
            pre {
                border-radius: 0px;
                border: none;
            }

            pre code {
                margin: -9.5px;
            }

        </style>
    </head>
    <body>
<?php if (array_key_exists("json", $_POST)):
    $str = $_POST["json"];
    $json = json_decode($str, true);

    $variables = $json["variables"];
    $info = $json["info"];

    $collectionName = $info["name"]; // nombre de la coleccion 
    $collectionDescription = $info["description"]; // descripcion de la coleccion

    $items = $json["item"];  ?>
            <div class="container-fluid">
                <ul class="nav nav-list nav-tree col-md-3" style="    height: 1150px;overflow-y: auto;">
                    <h1><?php echo $collectionName ?></h1>

                    <?php $class = "active" ?>
                    <?php foreach ($items as $id => $item): ?>

                        <?php if (is_array($item["item"]) && array_key_exists("item", $item)): ?>
                            <li class="tree trunk">
                                <ul class="nav nav-list">
                                    <li class="branch-header">
                                        <a class="tree-toggler icon-folder-close" href="#"><?php echo ($item["name"]); ?></a>
                                    </li>

                                    <?php foreach ($item["item"] as $key => $sub_item): ?>
                                        <li class="tree stem"><a class="icon-file stem-link <?php echo $class ?>" href="#tab_<?php echo $id . "_" . $key; ?>" data-toggle="pill"><?php echo $sub_item["name"]; ?></a></li>
                                        <?php $class = "" ?>
                                    <?php endforeach; ?>
                                </ul>
                            </li>

                        <?php else: ?>
                            <li ><a class="<?php echo $class ?>" href="#tab_<?php echo $id; ?>" data-toggle="pill"><?php echo $item["name"]; ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <div class="tab-content col-md-9">

                    <?php foreach ($items as $id => $item): ?>
                        <?php if (is_array($item["item"]) && array_key_exists("item", $item)): ?>

                            <?php $class = "active" ?>
                            <?php foreach ($item["item"] as $key => $sub_item): ?>
                                <div class="tab-pane <?php echo $class ?>" id="tab_<?php echo $id . "_" . $key; ?>">
                                    <?php $class = "" ?>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <?php
                                            $request = $sub_item["request"]; //solicitud
                                            $uri = (is_array($request["url"]) ? $request["url"]["raw"] : $request["url"]); // url
                                            $description = $request["description"]; // descripcion del endpoint
                                            ?>
                                            <h3>Descripción</h3>
                                            <p class="text-muted"><?php echo $request["description"]; ?></p>
                                            <h3>URI</h3>
                                            <code><?php echo $uri; ?></code>
                                            <h3>Method</h3>
                                            <code><?php echo $request["method"]; ?></code>
                                            

                                            <h3>Datos de Entrada</h3>
                                            <?php $bodyForm = $request["body"]; ?>
                                            <p></p>
                                            <?php if (!empty($bodyForm)): ?>
                                                <?php if($bodyForm["mode"] === 'formdata'): ?>
                                                <p>formdata</p>
                                                <?php $formdata = $bodyForm["formdata"]?>
                                                <table class="table table-bordered table-striped">
                                                    <?php foreach ($formdata as $body): ?>

                                                        <tr>
                                                            <th><?php echo ($body["key"]); ?></th>
                                                            <td><?php echo ($body["value"]); ?></td>
                                                        </tr>

                                                    <?php endforeach; ?>
                                                </table>
                                                <?php elseif($bodyForm["mode"] === 'raw'): ?>
                                                    <p>raw</p>
                                                    <pre><code  class="hljs json"><?php echo $request["body"]["raw"]; ?></code></pre>
                                                <?php endif; ?>
                                                
                                            <?php endif; ?>
                                            <?php $headers = $request["header"]; ?>
                                            <p></p>
                                            <?php if (!empty($headers)): ?>
                                                <p>headers</p>
                                                <table class="table table-bordered table-striped">
                                                    <?php foreach ($headers as $header): ?>

                                                        <tr>
                                                            <th><?php echo ($header["key"]); ?></th>
                                                            <td><?php echo ($header["value"]); ?></td>
                                                        </tr>

                                                    <?php endforeach; ?>
                                                </table>
                                            <?php endif; ?>
                                            
                                            
                                            
                                            
                                        

<?php

$curlNL = " \\\n";
$curl = "curl -X ".$request["method"];
$curl = $curl.$curlNL." '".($uri)."'";
$curlHeaders = "";
$javaHeaders = "";
if (!empty($headers)) {
    foreach ($headers as $header) {
        $curlHeaders .= $curlNL."-H '".$header["key"].": ".$header["value"]."'";
        $javaHeaders .= '                    .header("'.$header["key"].'", "'.$header["value"].'")
';
    }   
}
$curlRaw = "";
$curlBody = "";
$javaBody = "";
$javaRawParam = "";
$javaRaw = "";
$javaPayloadObj = "";
$javaPayload = "";
if (!empty($bodyForm)) {
    if($bodyForm["mode"] === 'formdata') {
        $formdata = $bodyForm["formdata"];
        foreach ($formdata as $body) {
            $curlBody .= $curlNL."-F '".$body["key"]."'='".$body["value"]."'";
            $javaBody .= '            form.param("'.$body["key"].'", "'.$body["value"].'");
';
        }

        if(!empty($javaBody)) {
            $javaPayloadObj = "payload";
            $javaPayload = "            Entity<Form> payload = Entity.entity(form, MediaType.APPLICATION_FORM_URLENCODED);";
            $javaBody = '            Form form = new Form();
        '.$javaBody;
        }
        
    } elseif($bodyForm["mode"] === 'raw') {
        $curlRaw =  $curlNL."-d '".$request["body"]["raw"]."'"; 
        $javaRawParam = "ObjectInput objectInput";
        $javaRaw = "            Entity<ObjectInput> payload = Entity.entity(objectInput, MediaType.APPLICATION_JSON);";
        $javaPayloadObj = "payload";
    }
}


$curl = $curl . $curlHeaders .$curlBody.$curlRaw;

// $item["name"]
// $sub_item["name"] 

$jaxrs ='import javax.ws.rs.client.*;
import javax.ws.rs.core.*;
import java.io.IOException;

public class ExampleEndpointClass {
    public ObjectOutput exampleMethod('.$javaRawParam.') {
            Client client = ClientBuilder.newClient();
'.$javaPayload.$javaRaw.'
'.$javaBody.'
            String endpoint = "'.$uri.'";

            Response response = client.target(endpoint)
                    .request(MediaType.WILDCARD)
'.$javaHeaders.'
                    .'.  strtolower($request["method"]).'('.$javaPayloadObj.');
            String json = response.readEntity(String.class);
            ObjectMapper mapper = new ObjectMapper();
            ObjectOutput output = null;
            if (response.getStatus() == 200) {
                try {
                    output = mapper.readValue(json, ObjectOutput.class);
                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
            client.close();

            return output;
        }
}
';
?>                                                    
                                            <h3>Codigo</h3>
                                            <div>
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li role="presentation" class="active">
                                                            <a href="#curl-<?php echo md5($sub_item."@".$uri) ?>" data-toggle="tab" aria-expanded="false">
                                                            cURL
                                                            </a>
                                                        </li>   

                                                         <li role="presentation" class="">
                                                            <a href="#java-<?php echo md5($sub_item."@".$uri) ?>" data-toggle="tab" aria-expanded="false">
                                                            Java (jaxrs-client)
                                                            </a>
                                                        </li>   
                                                </ul>
                                                <div class="tab-content">
                 
                                                    <div class="tab-pane active" id="curl-<?php echo md5($sub_item."@".$uri) ?>">
                                                        <pre><code  class="hljs bash"><?php echo $curl ?></code></pre>
                                                    </div>
                                                    <div class="tab-pane " id="java-<?php echo md5($sub_item."@".$uri) ?>">
                                                        <pre><code  class="hljs java"><?php echo $jaxrs ?></code></pre>
                                                    </div>

                                                </div>
                                            </div>
                                            
                                             


                                            <?php $responses = $sub_item["response"]; //respuestas     ?>
                                            <?php if (!empty($responses)): ?>
                                            <h3>Respuetas</h3>
                                            
                                            
                                            <div>
                                                    <ul class="nav nav-tabs" role="tablist">
                                                    <?php $class = "active" ?>
                                                        <?php foreach ($responses as $response): ?>
                                                            <li role="presentation" class="<?php echo $class ?>">
                                                                <a href="#responses-<?php echo $response["id"] ?>" data-toggle="tab" aria-expanded="false">
                                                                <?php echo $response["name"] ?>
                                                                </a>
                                                            </li>
                                                            <?php $class = "" ?>
                                                        <?php endforeach; ?>
                                                        
                                                    </ul>
                                                    <div class="tab-content">
                                                    <?php $class = "active" ?>
                                                        <?php foreach ($responses as $response): ?>
                                                            <div class="tab-pane <?php echo $class ?>" id="responses-<?php echo $response["id"] ?>">
                                                                <table class="table table-bordered">
                                                                    <tbody>
                                                                        <tr>
                                                                            <th style="width: 20%;"><?php echo $response["status"] ?></th>
                                                                            <td><?php echo $response["code"] ?></td>
                                                                        </tr>

                                                                    </tbody>
                                                                </table>
                                                                <pre><code  class="hljs json"><?php echo trim($response["body"]) ?></code></pre>
                                                            </div>
                                                            <?php $class = "" ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        
                                    <p></p>
                                    
                                    
                                                       
                                    
                                </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                
            </div>
            
<?php else: ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-lg-12">
                                <h1>Generar HTML de una colección Postman V2</h1>
                                <textarea name="json" class="form-control" rows="25" placeholder=""></textarea> 
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <p>&nbsp;</p>
                                <button type="submit" class="btn btn-success">Generar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php endif; ?>

        
        
<!-- Bootstrap Script -->        
<script src="https://code.jquery.com/jquery-2.2.2.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        $('.tree-toggler').click(function () {
            $(this).parent().parent().children('.tree.stem').toggle(300);
            $(this).toggleClass("icon-folder-open").animate(300);
            $(this).toggleClass("icon-folder-close").animate(300);
        });
    });
</script>
    </body>
</html>
