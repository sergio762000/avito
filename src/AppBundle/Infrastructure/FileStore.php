<?php


namespace AppBundle\Infrastructure;


use AppBundle\Domain\StoreSession;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\VarDumper\VarDumper;

class FileStore implements StoreSession
{
    const PATH_TO_STORE_FILE = '../var/storeForSessionId.txt';

    public function find()
    {
        $listSessionId = array();

        // TODO: Implement find() method.
        return $listSessionId;
    }

    public function findOne($uid)
    {
        if (!is_readable(self::PATH_TO_STORE_FILE)) {
            throw new AccessDeniedException(self::PATH_TO_STORE_FILE);
        }

        $listSession = array();
        $listSession = file(self::PATH_TO_STORE_FILE, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);

        $foundString = '';
        if ($listSession) {
            foreach ($listSession as $item) {
                $foundString = stristr($item, (string) $uid);
            }
        }

        return $foundString;

    }

    public function save(array $dataForSave)
    {
        try {
            // TODO: Implement save() method.

            $result = false;

            if (!is_readable(self::PATH_TO_STORE_FILE)) {
                if (!file_put_contents(self::PATH_TO_STORE_FILE, PHP_EOL, FILE_APPEND)) {
                    throw new AccessDeniedException(self::PATH_TO_STORE_FILE);
                }
            }

            $fileHandle = fopen(self::PATH_TO_STORE_FILE, "a+");
            if ($fileHandle) {
                fwrite($fileHandle, $dataForSave['sessionUID'] . '#' . $dataForSave['validUntil'] . '#' . $dataForSave['urlForNotification'] . PHP_EOL);
                fclose($fileHandle);
                $result = true;
            }

            return $result;
        } catch (\Exception $e) {
            return json_encode(array("message" => $e->getMessage()));
        }

    }

    public function delete($uid)
    {
        // TODO: Implement delete() method.
        return true;
    }

}
