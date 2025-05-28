<?php
function sanitize($data) {
    if (!is_array($data)) return htmlspecialchars(stripslashes(trim($data)));
    $tmp = [];
    foreach ($data as $k => $v) $tmp[$k] = sanitize($v);
    return $tmp;
}

function compressData($dataArray) {
    $jsonData = json_encode(sanitize($dataArray), JSON_UNESCAPED_UNICODE);
    if ($jsonData === false) return "";
    return base64_encode(gzdeflate($jsonData, 9));
}

function uncompressData($compressedData) {
    if (empty($compressedData)) return "";
    $uncompressed = gzinflate(base64_decode($compressedData));
    if ($uncompressed === false) return "";
    return json_decode($uncompressed, true);
}

$db_host = "localhost";
$db_user = "contoh";
$db_password = "C5C4tAJ5Ba5iS3MOv4J1bO6aWAW464";
$db_name = "blobdatastorage";
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

$data = []; // prepare blank array incase below processes failed
$last_modified = "";
$id = "";

if (isset($_REQUEST["simpan"])) {
    $compressedData = compressData($_REQUEST["data"]);
    $result = $conn->query(($mode_edit = $_REQUEST["simpan"] !== "baru") ? ("UPDATE `biodata` SET `data` = '{$compressedData}' WHERE `id` = " . intval($_REQUEST["simpan"])) : "INSERT INTO `biodata` (`data`) VALUES ('{$compressedData}')");
    header("location: index.php?id=" . ($mode_edit ? intval($_REQUEST["simpan"]) : $conn->insert_id));

}

if (isset($_REQUEST["id"])) {
    $result = $conn->query("SELECT * FROM `biodata` WHERE `id` = " . sanitize($_REQUEST["id"]) . " LIMIT 1");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data = uncompressData($row["data"]);
        $last_modified = $row["tgl_modifikasi"];
        $id = $_REQUEST["id"];
    }
}
?>
<!doctype html>
<html>
<head>
    <title>Sample of compressed storage</title>
</head>
<body>
    <div>
        <?php
        $result = $conn->query("SELECT `id` FROM `biodata` ORDER BY `id`");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc())
                echo "<a href=\"index.php?id=" . $row["id"] . "\" style=\"padding: 10px\">" . $row["id"] . "</a>";
        }
        ?>
        <a href="index.php" style="padding:10px">Baru</a>
    </div>
    <form action="index.php" method="post">
        <table>
            <tr>
                <td>ID</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><?php echo $id; ?></td>
            </tr>
            <tr>
                <td>Tanggal Modifikasi</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><?php echo $last_modified; ?></td>
            </tr>
            <tr>
                <td>Nama</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="text" name="data[0]" value="<?php echo $data[0]; ?>"/></td>
            </tr>
            <tr>
                <td>Alamat<br/>Nama Jalan &amp; Nomor Rumah</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><textarea name="data[1][0]"><?php echo $data[1][0];?></textarea></td>
            </tr>
            <tr>
                <td>Kelurahan / Desa</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="text" name="data[1][1]" value="<?php echo $data[1][1]; ?>"/></td>
            </tr>
            <tr>
                <td>Kecamatan</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="text" name="data[1][2]" value="<?php echo $data[1][2]; ?>"/></td>
            </tr>
            <tr>
                <td>Kabupaten / Kota</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="text" name="data[1][3]" value="<?php echo $data[1][3]; ?>"/></td>
            </tr>
            <tr>
                <td>Provinsi</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="text" name="data[1][4]" value="<?php echo $data[1][2]; ?>"/></td>
            </tr>
            <tr>
                <td>Kodepos</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="text" name="data[1][5]" value="<?php echo $data[1][5]; ?>"/></td>
            </tr>
            <tr>
                <td>Sudah Berkeluarga</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><select name="data[2][0]">
                    <option></option>
                    <option value="Sudah"<?php if ($data[2][0] == "Sudah") echo " selected";?>>Sudah</option>
                    <option value="Belum"<?php if ($data[2][0] == "Belum") echo " selected";?>>Sudah</option>
                </select></td>
            </tr>
            <tr>
                <td>Jumlah tanggungan (Istri dan Anak)</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="number" name="data[2][1]" value="<?php echo $data[2][1]; ?>"/></td>
            </tr>
            <tr>
                <td>Penghasilan Per Bulan</td>
                <td style="padding-left:10px;padding-right:10px">:</td>
                <td><input type="number" name="data[3]" value="<?php echo $data[3]; ?>"/></td>
            </tr>
        </table>
        <div style="margin-top:30px"><button type="submit" name="simpan" value="<?php echo empty($id) ? "baru" : $id; ?>">Kirim</button></div>
    </form>
</body>
</html>
