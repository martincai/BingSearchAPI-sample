<?php
/****
 * Simple PHP application for using the Bing Search API
*/

$acctKey  = '';
$rootUri = 'https://api.datamarket.azure.com/Bing/Search';

// Read the contents of the .html file into a string.
$contents = file_get_contents('bing_search_api.html');

if ($_POST['query'])
{
  // Encode the query and the single quotes that must surround it.
  $querybuilder = $_POST['query'];
  
  // Set site target.
  if ($_POST['Site'] !== '')
  {
    $querybuilder .= " site:{$_POST['Site']}";
  }
  
  // Encode the query and the single quotes that must surround it.
  $query = urlencode("'{$querybuilder}'");
  
  // Get the selected service operation (Web or Image).
  $serviceOp = $_POST['service_op'];

  // Construct the full URI for the query.
  $requestUri = "$rootUri/$serviceOp?Query=$query";
  if ($_POST['Top'] !== '')
  {
    $requestUri .= "&\$top={$_POST['Top']}";
  }
  if ($_POST['Skip'] !== '')
  {
    $requestUri .= "&\$skip={$_POST['Skip']}";
  }
  //if ($_POST['Format'] == 'JSON')
  //{
    $requestUri .= "&\$format=json";
  //}
  switch ($_POST['Adult'])
  {
    case 'Off':
      $requestUri .= '&Adult=%27Off%27';
      break;
    case 'Moderate':
      $requestUri .= '&Adult=%27Moderate%27';
      break;
    case 'Strict':
      $requestUri .= '&Adult=%27Strict%27';
      break;
  }
  if ($_POST['Market'] !== '')
  {
    $requestUri .= "&Market=%27{$_POST['Market']}%27";
  }
  if ($_POST['Latitude'] !== '')
  {
    $requestUri .= "&Latitude=%27{$_POST['Latitude']}%27";
  }
  if ($_POST['Longitude'] !== '')
  {
    $requestUri .= "&Longitude=%27{$_POST['Longitude']}%27";
  }
  
  if (isset($_POST['Options_DisableLocationDetection']) && isset($_POST['Options_EnableHighlighting']))
  {
    $requestUri .= '&Options=%27DisableLocationDetection%2BEnableHighlighting%27';
  }
  else if (isset($_POST['Options_EnableHighlighting']))
  {
    $requestUri .= '&Options=%27EnableHighlighting%27';
  }
  else if (isset($_POST['Options_DisableLocationDetection']))
  {
    $requestUri .= '&Options=%27DisableLocationDetection%27';
  }
  
  if (isset($_POST['WebSearchOptions_DisableHostCollapsing']) && isset($_POST['WebSearchOptions_DisableQueryAlterations']))
  {
    $requestUri .= '&WebSearchOptions=%27DisableQueryAlterations%2BDisableHostCollapsing%27';
  }
  else if (isset($_POST['WebSearchOptions_DisableHostCollapsing']))
  {
    $requestUri .= '&WebSearchOptions=%27DisableHostCollapsing%27';
  }
  else if (isset($_POST['WebSearchOptions_DisableQueryAlterations']))
  {
    $requestUri .= '&WebSearchOptions=%27DisableQueryAlterations%27';
  }

  // Encode the credentials and create the stream context.
  $auth = base64_encode("$acctKey:$acctKey");
  $data = array(
    'http' => array(
      'request_fulluri' => true,
      // ignore_errors can help debug – remove for production. This option added in PHP 5.2.10
      'ignore_errors' => true,
      'header'  => "Authorization: Basic $auth")
    );
  $context = stream_context_create($data);
  
  // Get the response from Bing.
  $response = file_get_contents($requestUri, 0, $context);
  $resultStr = "<p>URI: $requestUri</p>";

  // Format search results
  if (isset($_POST['format_results']))
  {  
    // Decode the response.
    $jsonObj = json_decode($response);
    
    // Parse each result according to its metadata type.
    foreach ($jsonObj->d->results as $value)
    {
      switch ($value->__metadata->type)
      {
        case 'WebResult':
          $resultStr .=
            //"<p><a href=\"{$value->Url}\">{$value->Title}</a><br />{$value->Description}</p>";
            "<p><b>{$value->Title}</b><br /><a href=\"{$value->Url}\" target=\"_blank\">{$value->DisplayUrl}</a><br />{$value->Description}</p>";
          break;
        case 'ImageResult':
          $resultStr .= 
            "<h4>{$value->Title} ({$value->Width}x{$value->Height}) " .
            "({$value->FileSize} bytes)</h4>" .
            "<a href=\"{$value->MediaUrl}\">" .
            "<img src=\"{$value->Thumbnail->MediaUrl}\"></a><br />";
          break;
      }
    }
  }
  else // Just print out the results
  {
    $resultStr .= $response;
  }

  // Substitute the results placeholder. Ready to go.
  $contents = str_replace('{RESULTS}', $resultStr, $contents);
}

echo $contents;
?>