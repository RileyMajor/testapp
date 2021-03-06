<?php

namespace Mentoring\Controller;

use Mentoring\Taxonomy\TermHydrator;
use Mentoring\Taxonomy\VocabularyNotFoundException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class ApiController
{
    public function getApprenticesAction(Application $app)
    {
        /** @var \Mentoring\User\UserService $userService */
        $userService = $app['user.manager'];

        $mentees = $userService->fetchMentees();

        $responseData = [];
        $hydrator = $app['user.hydrator'];
        foreach($mentees as $mentee) {
            $data = $hydrator->extract($mentee);
            $responseData[] = $data;
        }

        return new Response(json_encode($responseData), 200, ['Content-Type' => 'application/json']);
    }

    public function getMentorsAction(Application $app)
    {
        /** @var \Mentoring\User\UserService $userService */
        $userService = $app['user.manager'];

        $mentors = $userService->fetchMentors();

        $responseData = [];
        $hydrator = $app['user.hydrator'];
        foreach($mentors as $mentor) {
            $data = $hydrator->extract($mentor);
            $responseData[] = $data;
        }

        return new Response(json_encode($responseData), 200, ['Content-Type' => 'application/json']);
    }

    public function getTerms(Application $app, $vocabularyName, $termName = null)
    {
        /** @var \Mentoring\Taxonomy\TaxonomyService $taxonomyService */
        $taxonomyService = $app['taxonomy.service'];
        try {
            $vocabulary = $taxonomyService->fetchVocabularyByName($vocabularyName);
            $terms = $taxonomyService->fetchAllTerms($vocabulary);
            $termHydrator = new TermHydrator();

            $termData = [];
            foreach($terms as $term) {
                $termData[] = $termHydrator->extract($term);
            }

            return new Response(
                json_encode(
                    [
                        'vocabulary' => $vocabularyName,
                        'count' => count($termData),
                        'terms' => $termData
                    ]
                ),
                200,
                ['Content-Type' => 'application/json']
            );

        } catch (VocabularyNotFoundException $e) {
            return new Response(json_encode(['error' => $e->getMessage()]), 404, ['Content-Type' => 'application/json']);
        }

    }
}