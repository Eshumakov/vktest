<?php
class Base_Img
{
    const TABLE = 'images';

    //128 X 128
    const SIZE_S = 1;
    //500 X M
    const SIZE_M_W = 2;
    //M X 500
    const SIZE_M_H = 3;

    //orig
    const SIZE_O = 99;

    public static function getUrlById($id, $size = self::SIZE_O)
    {
        $fName = $id  . '_' . $size . '.jpeg';
        if ($size == self::SIZE_O) {
            $fName = $id  . '.jpeg';
        }

        return '/share/img/' . $fName;
    }

    public function allowedExt()
    {
        return [
            'jpg', 'jpeg', 'png', 'gif',
            'JPG', 'JPEG', 'PNG', 'GIF',
        ];
    }

    public function baseInsert($name, $size)
    {
        $db = Service_Db::i()->dbh();

        $select = $db->prepare('INSERT INTO ' . self::TABLE . ' (name, size) VALUES (?, ?)');
        if (!$select->execute([$name, $size])) {
            $select->errorInfo();
            return false;
        };

        return $db->lastInsertId();
    }

    public function hasFileToUpload($name)
    {
        if (empty($_FILES[$name]['size'])) {
            return ['ret' => 0, 'error' => 'no Image'];
        }

        $uploadsize = $_FILES[$name]['size'];
        if ($uploadsize > 1900750) {
            unlink($_FILES[$name]['tmp_name']);
            return ['ret' => 0, 'error' => 'file size too big'];
        }

        return ['ret' => 1];
    }

    public function uploadImg($name = 'upload')
    {

        $uploaddir = Base_Config::getSiteDir() . '/share/img/';
        $uploadsize = $_FILES[$name]['size'];
        $file = explode('.', $_FILES[$name]['name']);
        $extension = end($file);

        if ($uploadsize > 1900750) {
            unlink($_FILES[$name]['tmp_name']);
            return ['ret' => 0, 'error' => 'file size too big'];
        }


        if (!in_array($extension, $this->allowedExt())) {
            unlink($_FILES[$name]['tmp_name']);
            return ['ret' => 0, 'error' => 'wrong extension'];
        }

        $id = $this->baseInsert(basename($_FILES[$name]['name']), $uploadsize);
        $uploadfile = $uploaddir . $id . '.' . $extension;

        if (move_uploaded_file($_FILES[$name]['tmp_name'], $uploadfile)) {
            $this->imageToJpeg($uploadfile, $id);
            $this->sizesTransfer($id);
            return ['ret' => 1, 'id' => $id, 'ext' => $extension];
        }

        return ['ret' => 0, 'error' => 'some upload error'];
    }

    public function sizesConfig()
    {
        return [
            self::SIZE_S => ['w' => 128, 'h' => 128],
            self::SIZE_M_H => ['h' => 500],
            self::SIZE_M_W => ['w' => 500],
        ];
    }

    public function sizesTransfer($id)
    {
        $sizes = $this->sizesConfig();
        $uploaddir = Base_Config::getSiteDir() . '/share/img/';
        $fileName = $uploaddir . $id . '.jpeg';

        foreach ($sizes as $sizeId => $size) {
            $newFileName = $uploaddir . $id . '_' . $sizeId . '.jpeg';
            $w = !empty($size['w']) ? $size['w'] : null;
            $h = !empty($size['h']) ? $size['h'] : null;
            $this->image_resize($fileName, $newFileName, $w, $h, ($w && $h));
        }
    }

    function image_resize($src, $dst, $width = null, $height = null, $crop=0){

        if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";

        if (!$width) {
            $width = $w;
        }

        if (!$height) {
            $height = $h;
        }

        $type = strtolower(substr(strrchr($src,"."),1));
        if($type == 'jpeg') $type = 'jpg';
        switch($type){
            case 'bmp': $img = imagecreatefromwbmp($src); break;
            case 'gif': $img = imagecreatefromgif($src); break;
            case 'jpg': $img = imagecreatefromjpeg($src); break;
            case 'png': $img = imagecreatefrompng($src); break;
            default : return "Unsupported picture type!";
        }

        // resize
        if($crop){
            if($w < $width or $h < $height) $needMore = true;
            $ratio = max($width/$w, $height/$h);
            $h = $height / $ratio;
            $x = ($w - $width / $ratio) / 2;
            $w = $width / $ratio;
        }
        else{
            if($w < $width and $h < $height) $needMore = true;
            $ratio = min($width/$w, $height/$h);
            $width = $w * $ratio;
            $height = $h * $ratio;
            $x = 0;
        }

        $new = imagecreatetruecolor($width, $height);

        // preserve transparency
        if($type == "gif" or $type == "png"){
            imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        imagecopyresampled($new, $img, 0, 0, $x, 0, $width, $height, $w, $h);

        switch($type){
            case 'bmp': imagewbmp($new, $dst); break;
            case 'gif': imagegif($new, $dst); break;
            case 'jpg': imagejpeg($new, $dst); break;
            case 'png': imagepng($new, $dst); break;
        }
        return true;
    }

    function imageToJpeg($srcFile, $id) {
        list($width_orig, $height_orig, $type) = getimagesize($srcFile, $info);

        // Temporarily increase the memory limit to allow for larger images
        ini_set('memory_limit', '32M');

        switch ($type)
        {
            case IMAGETYPE_GIF:
                $image = imagecreatefromgif($srcFile);
                break;
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($srcFile);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($srcFile);
                break;
            default:
                throw new Exception('Unrecognized image type ' . $type);
        }

        // create a new blank image
        $newImage = imagecreatetruecolor($width_orig, $height_orig);

        // Copy the old image to the new image
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width_orig, $height_orig, $width_orig, $height_orig);

        // Output to a temp file
        $uploaddir = Base_Config::getSiteDir() . '/share/img/';
        $destFile = $uploaddir . $id.'.jpeg';
        imagejpeg($newImage, $destFile);

        // Free memory
        imagedestroy($newImage);

        if ( is_file($destFile) ) {
            return $destFile;
        }

        throw new Exception('Image conversion failed.');
    }
}