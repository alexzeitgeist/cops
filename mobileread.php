<?php

if (!class_exists('Base'))
{
  exit;
}

class MobileReadAttachment extends Base {
    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public static function getAttachmentByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select custom_column_2.id, custom_column_2.value as name
from books_custom_column_2_link
join custom_column_2 on custom_column_2.id = books_custom_column_2_link.value
where books_custom_column_2_link.book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new MobileReadAttachment ($post);
        }
        return NULL;
    }
}

class MobileReadThread extends Base {
    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public static function getThreadByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select custom_column_3.id, custom_column_3.value as name
from books_custom_column_3_link
join custom_column_3 on custom_column_3.id = books_custom_column_3_link.value
where books_custom_column_3_link.book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new MobileReadThread ($post);
        }
        return NULL;
    }
}

class MobileReadUploader extends Base {
    const ALL_UPLOADERS_ID = "cops:mruploaders";

    const UPLOADER_COLUMNS = "custom_column_4.id, custom_column_4.value as name, count(*) as count";

    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public function getUri () {
        return "?page=".parent::PAGE_UPLOADER_DETAIL."&id=$this->id";
    }

    public function getEntryId () {
        return self::ALL_UPLOADERS_ID.":".$this->id;
    }

    public static function getUploaderById ($uploaderId) {
        $result = parent::getDb ()->prepare('select ' . self::UPLOADER_COLUMNS . ' from custom_column_4 where id = ?');
        $result->execute (array ($uploaderId));
        $post = $result->fetchObject ();
        return new MobileReadUploader ($post);
    }

    public static function getUploaderByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select custom_column_4.id, custom_column_4.value as name
from books_custom_column_4_link
join custom_column_4 on custom_column_4.id = books_custom_column_4_link.value
where books_custom_column_4_link.book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new MobileReadUploader ($post);
        }
        return NULL;
    }

    public static function getCount() {
        return parent::getCountGeneric ("custom_column_4", self::ALL_UPLOADERS_ID, parent::PAGE_ALL_UPLOADERS);
    }

    public static function getAllUploaders() {
        $result = parent::getDb ()->query('select custom_column_4.id, custom_column_4.value as name, count(*) as count
from books_custom_column_4_link
join custom_column_4 on custom_column_4.id = books_custom_column_4_link.value
group by custom_column_4.id, custom_column_4.value
order by custom_column_4.value');
        $entryArray = array();
        while ($post = $result->fetchObject ())
        {
            $uploader = new MobileReadUploader ($post);
            array_push ($entryArray, new Entry ($uploader->name, $uploader->getEntryId (),
                str_format (localize("bookword", $post->count), $post->count), "text",
                array ( new LinkNavigation ($uploader->getUri ())), "", $post->count));
        }
        return $entryArray;
    }

}

class MobileReadVariant extends Base {
    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public static function getVariantByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select custom_column_5.id, custom_column_5.value as name
from books_custom_column_5_link
join custom_column_5 on custom_column_5.id = books_custom_column_5_link.value
where books_custom_column_5_link.book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new MobileReadVariant ($post);
        }
        return NULL;
    }
}

class MobileReadTitle extends Base {
    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public static function getTitleByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select custom_column_6.id, custom_column_6.value as name
from books_custom_column_6_link
join custom_column_6 on custom_column_6.id = books_custom_column_6_link.value
where books_custom_column_6_link.book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new MobileReadTitle ($post);
        }
        return NULL;
    }
}

class MobileReadLastUpdate extends Base {
    public $id;
    public $name;

    public function __construct($post) {
        $this->id = $post->id;
        $this->name = $post->name;
    }

    public static function getLastUpdateByBookId ($bookId) {
        $result = parent::getDb ()->prepare('select custom_column_8.id, custom_column_8.value as name
from custom_column_8
where custom_column_8.book = ?');
        $result->execute (array ($bookId));
        if ($post = $result->fetchObject ()) {
            return new MobileReadLastUpdate ($post);
        }
        return NULL;
    }
}
