<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use app\Models\Book;
use DB;
use Log;

class BooksTest extends TestCase
{
    public function test_book_list_page_no_books_found(): void
    {
        // * Delete all book records
        DB::table('books')->truncate();

        $response = $this->get('/books');
        $response->assertStatus(200);
        $response->assertSee('Either no book records are found in the database or no matching search results.');
        $response->assertSee('Search');
        $response->assertDontSee('Action');

        // * Book record edit button
        $response->assertDontSee('fa-regular fa-pen-to-square');

        // * Book record delete button
        $response->assertDontSee('fa-solid fa-trash');

        $response->assertDontSee('Export');
        $response->assertDontSee('generateTitle');
        $response->assertDontSee('generateAuthor');
    }

    public function test_book_list_page_with_book_records(): void
    {
        $testBook = Book::create([
            'title' => 'test_title',
            'author' => 'test_author'
        ]);

        $response = $this->get('/books');
        $response->assertStatus(200);
        $response->assertDontSee('Either no book records are found in the database or no matching search results.');
        $response->assertSee('Search');
        $response->assertSee('Action');

        // * Checks if book list view contains the created test book record
        $response->assertViewHas('books', function ($collection) use ($testBook) {
            return $collection->contains($testBook);
        });

        $response->assertSee('Export');
        $response->assertSee('generateTitle');
        $response->assertSee('generateAuthor');

        // * Delete $testBook from DB
        $testBook->delete();
    }

    public function test_book_list_page_sorted_by_title(): void
    {
        $testBookOne = Book::create([
            'title' => 'CCC_title',
            'author' => 'DDD_author'
        ]);

        $testBookTwo = Book::create([
            'title' => 'AAA_title',
            'author' => 'BBB_author'
        ]);

        $values = array('AAA_title', 'CCC_title');

        $response = $this->get('/books/sortedbytitle');
        $response->assertStatus(200);

        // * Checks if '$testBooktwo' is before '$testBookOne' in the the book list view sorted by title
        $response->assertSeeInOrder($values, $escaped = true);

        // * Delete all book records
        DB::table('books')->truncate();
    }

    public function test_book_list_page_sorted_by_author(): void
    {
        $testBookOne = Book::create([
            'title' => 'CCC_title',
            'author' => 'DDD_author'
        ]);

        $testBookTwo = Book::create([
            'title' => 'AAA_title',
            'author' => 'BBB_author'
        ]);

        $values = array('BBB_author', 'DDD_author');

        $response = $this->get('/books/sortedbyauthor');
        $response->assertStatus(200);

        // * Checks if '$testBooktwo' is before '$testBookOne' in the the book list view sorted by author
        $response->assertSeeInOrder($values, $escaped = true);

        // * Delete all book records
        DB::table('books')->truncate();
    }

    public function test_book_list_page_search_feature(): void
    {
        $testBookOne = Book::create([
            'title' => 'CCC_title',
            'author' => 'DDD_author'
        ]);

        $testBookTwo = Book::create([
            'title' => 'AAA_title',
            'author' => 'BBB_author'
        ]);

        $responseForTitleSearch = $this->get('/books?keyword=CCC');

        // * Checks if book list view contains testBookOne which has 'CCC' in its 'title'
        $responseForTitleSearch->assertViewHas('books', function ($collection) use ($testBookOne) {
            return $collection->contains($testBookOne);
        });

        // * Checks if book list view does not contain testBookTwo record
        $responseForTitleSearch->assertViewHas('books', function ($collection) use ($testBookTwo) {
            return $collection->doesntContain($testBookTwo);
        });

        $responseForAuthorSearch = $this->get('/books?keyword=BBB');

        // * Checks if book list view contains testBookTwo which has 'BBB' in its 'author' record
        $responseForAuthorSearch->assertViewHas('books', function ($collection) use ($testBookTwo) {
            return $collection->contains($testBookTwo);
        });

        // * Checks if book list view does not contain testBookOne record
        $responseForAuthorSearch->assertViewHas('books', function ($collection) use ($testBookOne) {
            return $collection->doesntContain($testBookOne);
        });

        // * Delete all book records
        DB::table('books')->truncate();
    }

    public function test_create_page(): void
    {
        $response = $this->get('/books/create');
        $response->assertStatus(200);
        $response->assertSee('Please enter the title of the book.');
        $response->assertSee('Please enter the author of the book.');
        $response->assertSee('Create');
    }

    public function test_api_returns_created_book(): void
    {
        $response = $this->post('/books', [
            'title' => 'another_test_title',
            'author' => 'another_test_author',
        ]);

        $this->assertCount(1, Book::all());

        // * Delete all book records
        DB::table('books')->truncate();
    }

    public function test_api_failed_to_created_book(): void
    {
        $response = $this->post('/books', [
            'title' => '',
            'author' => 'another_test_author',
        ]);

        $this->assertCount(0, Book::all());
    }

    public function test_update_page(): void
    {
        $testBook = Book::create([
            'title' => 'test_title',
            'author' => 'test_author'
        ]);

        $response = $this->get("/books/update/{$testBook->id}");
        $response->assertStatus(200);
        $response->assertSee('Update');
        $response->assertSee('test_title');
        $response->assertSee('test_author');

        // * Delete $testBook from DB
        $testBook->delete();
    }

    public function test_api_returns_updated_book(): void
    {
        $testBook = Book::create([
            'title' => 'test_title',
            'author' => 'test_author'
        ]);

        $response = $this->post("/books/update/{$testBook->id}", [
            'title' => 'updated_test_title',
            'author' => 'updated_test_author',
        ]);

        $book = Book::findOrfail($testBook->id);

        $this->assertStringContainsString('updated_test_title', $book->title);
        $this->assertStringContainsString('updated_test_author', $book->author);

        // * Delete $testBook from DB
        $testBook->delete();
    }

    public function test_api_failed_to_updated_book(): void
    {
        $testBook = Book::create([
            'title' => 'test_title',
            'author' => 'test_author'
        ]);

        $response = $this->post("/books/update/{$testBook->id}", [
            'title' => 'test_title',
            'author' => '',
        ]);

        $book = Book::findOrfail($testBook->id);

        $this->assertStringContainsString('test_title', $book->title);
        $this->assertStringContainsString('test_author', $book->author);

        // * Delete $testBook from DB
        $testBook->delete();
    }

    public function test_api_successfully_delete_book(): void
    {
        $testBook = Book::create([
            'title' => 'test_title',
            'author' => 'test_author'
        ]);

        $this->assertCount(1, Book::all());

        $response = $this->delete("/books", $testBook->toArray());
        
        $this->assertCount(0, Book::all());
    }
}
