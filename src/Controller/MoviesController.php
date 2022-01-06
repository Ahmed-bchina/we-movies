<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MoviesController extends AbstractController
{
    /**
     * @Route("/", name="movies")
     */
    public function index(Request $request, HttpClientInterface $httpClient): Response
    {
        // get api url and api key params from .env
        $movie_db_api_key = $this->getParameter('app.THE_MOVIE_DB_API_KEY');
        $movie_db_url = $this->getParameter('app.THE_MOVIE_DB_URL');
        $base_movie_url = $this->getParameter('app.BASE_MOVIE_URL');
        
        $movies = null;

        if ($request->isXmlHttpRequest())
        {
            // Get data
            $gender_id = intval($request->request->get('gender_id'));
            $gender_name = $request->request->get('gender_name');

            // get movies
            $movies = $httpClient->request('GET', $movie_db_url.'discover/movie?api_key='.$movie_db_api_key.'&language=fr&sort_by=popularity.desc&include_adult=false&include_video=false&page=1&with_watch_monetization_types=flatrate&with_genres='.$gender_name);
            dump($movies);die;
        }

        // get best movies (top rated movie)
        $top_rated_movies = $httpClient->request('GET', $movie_db_url.'movie/top_rated?api_key='.$movie_db_api_key.'&language=fr&page=1');

        // we take the first movie in the list 
        $top_rated_movie = $top_rated_movies->toArray()['results'][0];

        // get the list all cinema genders 
        $genders = $httpClient->request('GET', $movie_db_url.'genre/movie/list?api_key='.$movie_db_api_key.'&language=fr', [
            'query' => [
                'sort' => 'created'
            ]
        ]);

        if(!isset($movies)) {
            // get movies
            $movies = $httpClient->request('GET', $movie_db_url.'discover/movie?api_key='.$movie_db_api_key.'&language=fr&sort_by=popularity.desc&include_adult=false&include_video=false&page=1&with_watch_monetization_types=flatrate');
        }


        return $this->render('movies/index.html.twig', [
            'base_movie_url'      => $base_movie_url,
            'top_rated_movie'     => $top_rated_movie,
            'genders'             => $genders->toArray()['genres'],
            'movies'             => $movies->toArray()['results']
        ]);




        // $httpClient = HttpClient::create();
        // $response = $httpClient->request('GET', 'https://api.github.com/repos/symfony/symfony-docs');



        // return $this->render('movies/index.html.twig', [
        //     'controller_name' => 'MoviesController',
        // ]);
    }
}
