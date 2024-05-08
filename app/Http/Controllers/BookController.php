<?php

namespace App\Http\Controllers;

use App\Models\Book;
use DOMDocument;
use ZipArchive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;


class BookController extends Controller
{
    
    public function renderListView(Request $request) {
        /**
        * This function will render the book list view as well as display all book records inside the DB.
        * 
        * @param request
        * @return view
        */ 

        // * '$books' variable contains all book records from the 'books' table inside DB in descending order by date of creation.
        $books = Book::orderby('created_at', 'DESC');

        // * This 'if' condition filters all records in '$books' variable to records matching both the 'title' and 'author' column in DB with the search keyword provided.
        if (!empty($request->keyword)) {
            $books->where('title', 'like', '%'.$request->keyword.'%')
                ->orWhere('author', 'like', '%'.$request->keyword.'%'); 
        }
        
        // * This snippet sets the paginate count for all book records inside '$books'
        $books = $books->paginate(10);

        return view(
            'books.list',
            [
                'books' => $books
            ]
        );
    } //: renderListView


    public function renderTitleSortedListView() {
        /**
        * This function will render the book list view as well as display all book records inside the DB sorted by title.
        * 
        * @return view
        */ 

        // * '$books' variable contains all book records from the 'books' table inside DB in descending order by title.
        $books = Book::orderby('title', 'ASC');

        // * This snippet sets the paginate count for all book records inside '$books'
        $books = $books->paginate(10);

        return view(
            'books.list',
            [
                'books' => $books
            ]
        );
    } //: renderTitleSortedListView


    public function renderAuthorSortedListView() {
        /**
        * This function will render the book list view as well as display all book records inside the DB sorted by author.
        * 
        * @return view
        */ 

        // * '$books' variable contains all book records from the 'books' table inside DB in descending order by author.
        $books = Book::orderby('author', 'ASC');

        // * This snippet sets the paginate count for all book records inside '$books'
        $books = $books->paginate(10);

        return view(
            'books.list',
            [
                'books' => $books
            ]
        );
    } //: renderAuthorSortedListView


    public function renderCreateView() {
        /**
        * This function will render the create book view
        *
        * @return view
        */ 

        return view('books.create');
    } //: renderCreateView


    public function createBook(Request $request) {
        /**
        * This function will create a book record and save it to DB
        * 
        * @param request
        * @return view
        */ 

        // * Checks whether a title and an author has been provided when making a new book record.
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'author' => 'required',
        ]);

        // * This snippet will be executed if validation of '$validator' fails.
        if ($validator->fails()) {
            return redirect()->route('books.renderCreateView')->withInput()->withErrors($validator);
        }

        // * Save new record of Books to 'books' table inside DB using the provided value for title and author.
        $book = new Book();
        $book->title = $request->title;
        $book->author = $request->author;
        $book->save();

        // * This snippet will redirect the user to the book list page and will also display a success message.
        return redirect()->route('books.renderListView')->with('success', 'New book added successfully.');
    } //: createBook


    public function renderUpdateView($id) {
        /**
        * This function will render the update book view
        * 
        * @param id
        * @return View
        */

        $book = Book::findOrfail($id);

        return view(
            'books.update',
            [
                'book' => $book
            ]
        );
    } //: renderUpdateView


    public function updateBook($id, Request $request) {
        /**
        * This function will update an existing book record and save changes to DB
        * 
        * @param request
        * @return view
        */ 

        // * '$book' contains the book record the matches the book record's ID
        $book = Book::findOrfail($id);

        // * Checks whether a title and an author has been provided when updating a book record.
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'author' => 'required',
        ]);

        // * This snippet will be executed if validation of '$validator' fails.
        if ($validator->fails()) {
            return redirect()->route('books.renderUpdateView', $book->id)->withInput()->withErrors($validator);
        }

        // * Update the Book record using the new value provided for title and author.
        $book->title = $request->title;
        $book->author = $request->author;
        $book->save();

        // * This snippet will redirect the user to the book list page and will also display a success message.
        return redirect()->route('books.renderListView')->with('success', 'Book updated successfully.');
    } //: updateBook

    
    public function deleteBook(Request $request) {
        /**
        * This function will delete an existing book record inside the DB
        * 
        * @param request
        * @return view
        */ 

        // * '$book' contains the book record the matches the book record's ID
        $book = Book::findOrfail($request->id);

        if ($book == null) {
            // * This snippet will display the error message on the book list view
            session()->flash('error', 'Book record not found.');

            return response()->json([
                'status' => false,
                'message' => 'Book record not found.'
            ]); 
        } else {
            $book->delete();
            
            // * This snippet will display the success message on the book list view
            session()->flash('success', 'Book deleted successfully');
            
            return response()->json([
                'status' => true,
                'message' => 'Book deleted successfully.'
            ]); 
        }
    } //: deleteBook


    public function generateCSVAndXML(Request $request) {
        /**
        * This function will generate both CSV and XML files based on options selected in the browser of
        * either 'title' only, 'author' only or both 'title' and 'author'.

        * @param request
        * @return view
        */ 

        // ! CSV SNIPPET - This will generate a CSV file of 'books' table in DB
        // * '_csvWriteHelper' function writes csv content based on export options selected in the browser.
        function _csvWriteHelper(
            $isCsvHeader,
            $includeTitleInExport,
            $includeAuthorInExport,
            $fp,
            $row
        ) {
            if (
                $includeTitleInExport === "on"
                && $includeAuthorInExport === null
            ) {

                if ($isCsvHeader) {
                    fputcsv($fp, array('Title'));
                } else {
                    fputcsv($fp, array($row->title));    
                }

            } 
            else if (
                $includeTitleInExport === null
                && $includeAuthorInExport === "on"
            ) {

                if ($isCsvHeader) {
                    fputcsv($fp, array('Author'));
                } else {
                    fputcsv($fp, array($row->author));
                }
                
            } else {

                if ($isCsvHeader) {
                    fputcsv($fp, array('Title', 'Author'));
                } else {
                    fputcsv($fp, array($row->title, $row->author)); 
                }

            }
        } //: _csvWriteHelper

        // * '$books' variable contains all book records from the 'books' table inside DB in descending order by date of creation.
        $books = Book::latest()->get();
        $includeTitleInExport = $request->generateTitle;
        $includeAuthorInExport = $request->generateAuthor;

        $rules = array(
            'generateTitle' => 'required_without:generateAuthor',
            'generateAuthor' => 'required_without:generateTitle',
        );
        
        $customMessages = array(
            'required_without' => "Either 'Title' or 'Author' must be checked for exporting files.",
        );

        $validator = Validator::make(
            $request->all(), 
            $rules,
            $customMessages
        );

        // * This snippet will be executed if validation of '$validator' fails with a custom message
        if ($validator->fails()) {
            return redirect()->route('books.renderListView')->withInput()->withErrors($validator);
        }

        $csvFile = "books.csv";
        $fp = fopen($csvFile, "w+");
        $headers = array('Content-Type' => 'text/csv');
        
        // * This 'csvHelper' call is for the header content of the csv file.
        _csvWriteHelper(
            $isCsvHeader=true,
            $includeTitleInExport=$includeTitleInExport,
            $includeAuthorInExport=$includeAuthorInExport,
            $fp=$fp,
            $row=null
        );

        foreach($books as $row) {
            // * This 'csvHelper' call is for writing the body content of each row of the csv file.
            _csvWriteHelper(
                $isCsvHeader=false,
                $includeTitleInExport=$includeTitleInExport,
                $includeAuthorInExport=$includeAuthorInExport,
                $fp=$fp,
                $row=$row
            );
        }

        fclose($fp);

        // ! XML SNIPPET - This will convert the newly created CSV file into an XML file
        // * this opens the newly created 'books.csv' file
        $inputFile  = fopen($csvFile, 'rt');
        $xmlFile = 'books.xml';

        // * Get the headers of the 'books.csv'
        $headersForXML = fgetcsv($inputFile);

        // * Create a new dom document with pretty formatting
        $doc  = new DomDocument();
        $doc->formatOutput = true;

        // * Add the root <books> node to the document
        $root = $doc->createElement('books');
        $root = $doc->appendChild($root);

        // * Loop through each row creating a <book> node with the correct data
        while (($row = fgetcsv($inputFile)) !== FALSE) {
            $container = $doc->createElement('book');
            foreach($headersForXML as $i => $header)
                {
                    $child = $doc->createElement($header);
                    $child = $container->appendChild($child);
                    $value = $doc->createTextNode($row[$i]);
                    $value = $child->appendChild($value);
                }

            $root->appendChild($container);
        }

        $strxml = $doc->saveXML();
        $handle = fopen($xmlFile, "w");
        fwrite($handle, $strxml);
        fclose($handle);

        // ! COMPRESS BOTH CSV AND XML FILES INTO A ZIP FILE
        // * The below snippet will add 'books.csv' and 'books.xml' to a zip file
        $zip = new ZipArchive;
        $zipFileName = 'books.zip';

        if ($zip->open(public_path($zipFileName), ZipArchive::CREATE) === TRUE) {
            $filesToZip = [
                public_path($csvFile),
                public_path($xmlFile),
            ];

            foreach ($filesToZip as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();

            // * This will delete the generated 'books.csv' and 'books.xml' after it has been added to a zip file
            File::delete(public_path($csvFile));
            File::delete(public_path($xmlFile));

            // * The zip file generated will be downloaded and will be erased automatically afterwards
            $headers = [
                'Content-Type' => 'application/zip',
            ];

            $response = Response::download(
                public_path($zipFileName),
                'books.zip',
                $headers
            )->deleteFileAfterSend(true);

            return $response;

        } else {
            return "Failed to create the zip file.";
        }
    } //: generateCSVAndXML
}
