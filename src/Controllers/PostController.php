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
        
        try {
            
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
            
            return $stmt->rowCount() > 0;
                        
        } catch (PDOException $e) {
            
            //Unique constraint violation MySQL code 

            //During development dont catch specific exceptions yet

            /* if ($e->getCode() == 23000) {
                
                if (str_contains($e->getMessage(), 'slug')) {
                    throw new \Exception("Slug already exists");
                }
                if (str_contains($e->getMessage(), 'title')) {
                    throw new \Exception("Title already exists");
                }

                throw new \Exception("Duplicate entry");

            } */
            
            // Rethrow any other exceptions
            throw $e;
        }

    }

    /**
     * Update an already existing post
     * 
     * 
     * 
     * @return true on success
     * @return false on failure
     * @throws PDOException
     */
    public function update(string $old_slug, array $updated_post): bool {
        
        try {
            
            $post = new Post($updated_post);

            $sql = "UPDATE posts
                SET title = :title,
                    slug = :slug,
                    technology = :technology,
                    date = :date,
                    read_time_estimation = :read_time_estimation,
                    author_name = :author_name,
                    author_degree = :author_degree,
                    summary = :summary,
                    content = :content, 
                    conclusion = :conclusion,
                    tags = :tags
                WHERE slug = :old_slug 
            ";
            
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
                ':tags' => $post->tags,
                ':old_slug' => $old_slug
            ];

            $stmt = $this->db->query($sql, $params);
            
            return $stmt->rowCount() > 0;
                        
        } catch (PDOException $e) {
            
            //Unique constraint violation MySQL code 

            //During development dont catch specific exceptions yet

            /* if ($e->getCode() == 23000) {
                
                if (str_contains($e->getMessage(), 'slug')) {
                    throw new \Exception("Slug already exists");
                }
                if (str_contains($e->getMessage(), 'title')) {
                    throw new \Exception("Title already exists");
                }

                throw new \Exception("Duplicate entry");

            } */
            
            // Rethrow any other exceptions
            throw $e;
        }

    }    

    /**
     * Return Post or null
    */
    public function getPostById(int $id): ?Post {
        
        $postData = $this->db->query(
            "SELECT 
                title, slug, technology, date, 
                read_time_estimation, author_name, 
                author_degree, summary, content, 
                conclusion, tags 
            FROM posts WHERE slug = ?",
            [$id]
        )->fetch();
        
        return $postData ? new Post($postData) : null;
    }


    public function getPostBySlug(string $slug): ?Post {

        $postData = $this->db->query(
            "SELECT 
                title, slug, technology, date, 
                read_time_estimation, author_name, 
                author_degree, summary, content, 
                conclusion, tags 
            FROM posts WHERE slug = ?",
            [$slug]
        )->fetch();
        
        return $postData ? new Post($postData) : null;
    }

}


?>