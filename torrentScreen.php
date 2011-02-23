<?php
/*
 * Copyleft (<3) 2011, Movimento Partido Pirata Português - www.partidopiratapt.eu
 * All rights not reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted
 * provided that the following conditions are met:
 *    * Redistributions of source code must retain the above copyright notice, this list of
 *      conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice, this list of
 *      conditions and the following disclaimer in the documentation and/or other materials
 *       provided with the distribution.
 *    * Neither the name of the Partido Pirata Português nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific prior written
 *      permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
 * OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
 * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */
global $scriptPath, $utorrentImgPath, $utorrentImgPath, $fontSize, $fontfile;
$scriptPath = dirname(__FILE__);
//$scripturl = 'localhost/TorrentImage';
$utorrentImgPath = $scriptPath . '/utorrent.png';
$fontfile = $scriptPath . '/TAHOMA.TTF';
$fontSize = 8;





if (!file_exists($utorrentImgPath))
{
    die('Ops! The torrent program base image is not found... If you are the admin, fix it now!');
}
if (!file_exists($fontfile))
{
    die('Ops, the font file seems to be missing in action...');
}
$submit = false;
if (!empty($_GET['tname']) && !empty($_GET['tsize']) && !empty($_GET['trecv']))
{
    $submit = true;
    //the torrent title
    $torrentName = urldecode($_GET['tname']);
    //the torrent size in MB
    $torrentSize = intval($_GET['tsize'], 10);
    $torrentRcv = intval($_GET['trecv'], 10);
    $torrentSizeUnit = 'MB';
    $torrentRcvUnit = 'MB';
    //$originalFields = array ('tsize' => $torrentSize, 'trcv' => $torrentRcv, 'tname' => $torrentName);
    //normalize the torrent name size
    $titleMaxSize = 27;
    if (strlen($torrentName) > $titleMaxSize)
    {
        $torrentName = substr($torrentName, 0, $titleMaxSize) . '...';
    }
    //normalize the Torrent size and received size to the right units (MB or GB or TB)
    if ($torrentRcv < 0)
    {
        $torrentRcv = $torrentSize > 2 ? mt_rand(1, $torrentSize) : mt_rand(1, 2500);
    }
    if ($torrentSize <= $torrentRcv)
    {
        $torrentSize = mt_rand($torrentRcv + 1, $torrentRcv * 2);
    }
    if ($torrentSize <= 0)
    {
        $torrentSize = mt_rand(1, 2500);
    }
    //calculate the percentage done
    $torrentDonePercent = round($torrentRcv * 100 / $torrentSize, 1);

    if ($torrentSize >= 1000000)
    {
        $torrentSize = $torrentSize / 1024 / 1024;
        $torrentSizeUnit = 'TB';
    }
    elseif ($torrentSize >= 1000)
    {
        $torrentSize = $torrentSize / 1024;
        $torrentSizeUnit = 'GB';
    }
    if ($torrentRcv >= 1000000)
    {
        $torrentRcv = $torrentRcv / 1024 / 1024;
        $torrentRcvUnit = 'TB';
    }
    elseif ($torrentRcv >= 1000)
    {
        $torrentRcv /= 1024;
        $torrentRcvUnit = 'GB';
    }
    $torrentSize = round($torrentSize, 2);
    $torrentRcv = round($torrentRcv, 2);


    //load the base image
    $img = imagecreatefrompng($utorrentImgPath);
    if (!$img)
    {
        die('Unable to load the base image...');
    }
    $blackColor = imagecolorallocate($img, 0, 0, 0);
    $whiteColor = imagecolorallocate($img, 255, 255, 255);

    //------------ IMAGE EDIT ------------
    //torrent title
    imagettftext($img, $fontSize, 0, 56, 120, $whiteColor, $fontfile, $torrentName);
    //torrent size
    $xPos = strlen($torrentSize) > 3 ? 268 : 275;
    imagettftext($img, $fontSize, 0, $xPos, 120, $whiteColor, $fontfile, $torrentSize . ' ' . $torrentSizeUnit);
    //torrent received (size)
    $xPos = strlen($torrentRcv) > 2 ? 347 : 357;
    imagettftext($img, $fontSize, 0, $xPos, 120, $whiteColor, $fontfile, $torrentRcv . ' ' . $torrentRcvUnit);
    //torrent complete percent
    $xPos = strlen($torrentDonePercent) > 2 ? 410 : 415;
    imagettftext($img, $fontSize, 0, $xPos, 120, $blackColor, $fontfile, $torrentDonePercent . '%');


    $clientsArr = array ();
    for ($i = 0; isset($_GET['tc' . $i . '_ip']); $i++)
    {
        $key = 'tc' . $i;
        if (empty($_GET[$key . '_ip']))
        {
            continue;
        }
        $ip = $_GET[$key . '_ip'];
        //
        $client = empty($_GET[$key . '_client']) ? 'BitTorrent 7.2' : $_GET[$key . '_client'];

        $flags = empty($_GET[$key . '_flags']) ? '' : $_GET[$key . '_flags'];

        $complete = empty($_GET[$key . '_complete']) ? -1 : intval($_GET[$key . '_complete'], 10);
        if ($complete < 0 || $complete > 100)
        {
            $complete = mt_rand(0, 100);
        }
        //random stuff
        $receptionSpeed = $complete < 100 ? getRandomFloat(0, 200, 1) . 'kB/s' : '';
        $uploadSpeed = $complete > 3 && $torrentRcv  > 10 ? getRandomFloat(0, 50, 1) . 'kB/s' : '';
        $requests = !empty ($receptionSpeed) ? mt_rand(1, 5) : 0 . ' | ' . !empty ($uploadSpeed) ? mt_rand(1, 5) : 0;
        $clientsArr[] = array ('ip' => $ip, 'client' => $client, 'flags' => $flags, 'complete' => $complete,
            'download' => $receptionSpeed, 'upload' => $uploadSpeed, 'requests' => $requests);
    }
    if (count($clientsArr) > 0)
    {
        createClientList($img, $clientsArr);
    }
    //----------- END IMAGE EDIT ----------------
    //output the image
    header("Content-type: image/png");
    imagepng($img);

    imagedestroy($img);
}
//check how many client inputs will be shown
$clientNfiels = (!empty($_GET['n'])) ? intval($_GET['n'], 10) : 0;
if ($clientNfiels <= 0 || $clientNfiels > 16)
{
    $clientNfiels = 16;
}

function createClientList($img, array $clientArray)
{
    global $fontSize, $fontfile;
    if (!$img || !$clientArray) return;

    $blackColor = imagecolorallocate($img, 0, 0, 0);

    $y_base = 289;
    $y_inc = 19;
    $rowIndex = 0;

    foreach ($clientArray as $client)
    {
        $y_row = $y_base + $y_inc * $rowIndex;
        //ip
        imagettftext($img, $fontSize, 0, 60, $y_row, $blackColor, $fontfile, $client['ip']);
        //TODO: Sanitize ip input
        //client 
        imagettftext($img, $fontSize, 0, 205, $y_row, $blackColor, $fontfile, $client['client']);
        //TODO: Sanitize client input
        //flags
        imagettftext($img, $fontSize, 0, 363, $y_row, $blackColor, $fontfile, $client['flags']);
        //TODO: Sanitize flags input
        //complete %
        imagettftext($img, $fontSize, 0, 431, $y_row, $blackColor, $fontfile, $client['complete']);
        
        //random download
        imagettftext($img, $fontSize, 0, 494, $y_row, $blackColor, $fontfile, $client['download']);
        //random upload
        imagettftext($img, $fontSize, 0, 574, $y_row, $blackColor, $fontfile, $client['upload']);
        //random requests
        imagettftext($img, $fontSize, 0, 629, $y_row, $blackColor, $fontfile, $client['requests']);
        $rowIndex++;
    }
}

function getRandomFloat($min, $max, $precision = 3)
{
    return round($min + ($max - $min) * mt_rand(0, 32767) / 32767, $precision);
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Geradores de Screenshots de Torrents</title>
    </head>
    <body>
        <h1>Gerador de <i>Screenshots</i> de <i>Torrents</i></h1>
        <p>Criado para demonstrar que este tipo de imagens e registos de programas podem ser facilmente falsificados.<br/>
            <b>Nota</b>: A utilização dos resultados obtidos é inteiramente da responsabilidade do utilizador em questão.</p>
        <p><a href="http://partidopiratapt.eu">Visite o site do Movimento Partido Pirata Português</a></p>
        <h3>Torrent</h3>
        <form action="">

            <p><label>Nome do <i>Torrent</i>:
                    <input type="text" name="tname" value="ubuntu.iso" size="30" />
                </label></p>
            <p><label>Tamanho:
                    <input type="number" name="tsize" value="675" size="8" />MB
                </label></p>
            <p><label>Recebido:
                    <input type="number" name="trecv" value="13" size="8" />MB
                </label></p>
            <p><input type="submit" value="&quot;Submeter&quot;" /></p>
            <h3>Clientes a descarregar e partilhar o Torrente:</h3>
            <?php
            echo '<p><label>Endereço IP ou <i>Hostname</i>: <input type="text" name="tc0_ip" value="192.168.1.1" />&nbsp;&nbsp;</label>';
            echo '<label>Software do cliente: <input type="text" name="tc0_client" value="BitTorrent"  />&nbsp;&nbsp;</label>';
            echo '<label>Flags: <input type="text" name="tc0_flags" size="8" /></label>&nbsp;&nbsp;';
            echo '<label>Quantidade Completada (em %): <input type="number" name="tc0_complete" value="100" size="4" /></label>&nbsp;&nbsp;</p>';
            for ($i = 1; $i < $clientNfiels; $i++)
            {
                echo '<p><label>Endereço IP ou <i>Hostname</i>: <input type="text" name="tc' . $i . '_ip" /></label>&nbsp;&nbsp;';
                echo '<label>Software do cliente: <input type="text" name="tc' . $i . '_client"  /></label>&nbsp;&nbsp;';
                echo '<label>Flags: <input type="text" name="tc' . $i . '_flags" size="8"  /></label>&nbsp;&nbsp;';
                echo '<label>Quantidade Completada (em %): <input type="number" name="tc' . $i . '_complete" size="4" /></label>&nbsp;&nbsp;</p>';
            }
            ?>
            <p><input type="submit" value="&quot;givemethemoney&quot;" /></p>
        </form>

    </body>
</html>
