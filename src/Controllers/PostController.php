<?php

namespace App\Controllers;

use App\Database\Database;
use App\Models\Post;
use PDOException;

class PostController{

    public $db;

    public function __construct(){
        $this->db = Database::getInstance();

    }

    // Return latest 5 posts
    public function latest(int $limit = 1): array {

        $postsData = $this->db->query(
            "SELECT 
                title, slug, technology, date,
                read_time_estimation, summary
            FROM posts ORDER BY date LIMIT $limit;"
        )->fetchAll();
        
        return array_map(function($postsData){
            return new Post($postsData);

        }, $postsData);
        

    }

    /**
     * Add new post
     * 
     * @return true on success
     * @return false on failure
     * @throws PDOException
     */
    public function new(array $post): bool {
        
        $post = new Post($post);
        
        $sql = "INSERT INTO posts 
                (title, slug, technology, date, 
                read_time_estimation, author_name, 
                author_degree, summary, content, 
                conclusion, tags) 
                VALUES 
                (:title, :slug, :technology, :date, 
                :read_time_estimation, :author_name, 
                :author_degree, :summary, :content, 
                :conclusion, :tags)";
        
        $params = [
            ':title' => $post->title,
            ':slug' => $post->slug,
            ':technology' => $post->technology,
            ':date' => $post->date,
            ':read_time_estimation' => $post->read_time_estimation,
            ':author_name' => $post->author_name,
            ':author_degree' => $post->author_degree,
            ':summary' => $post->summary,
            ':content' => $post->content,
            ':conclusion' => $post->conclusion,
            ':tags' => $post->tags
        ];

        $stmt = $this->db->query($sql, $params);
        
        if ( $stmt->rowCount() > 0 ) {
            return true;
        
        }

        return false;
    }

    /**
     * Return Post or null
    */
    public function getPostById(int $id): ?Post {
        
        $postData = $this->db->query(
            "SELECT * FROM posts WHERE id = ?",
            [$id]
        )->fetch();
        
        return $postData ? new Post($postData) : null;
    }


    public function getPostBySlug(string $slug): ?Post {

        $postData = $this->db->query(
            "SELECT * FROM posts WHERE slug = ?",
            [$slug]
        )->fetch();
        
        return $postData ? new Post($postData) : null;
    }

}


?>