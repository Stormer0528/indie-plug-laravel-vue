<?php

use Illuminate\Support\Facades\Route;


// this file contains all the admin routes
// which are accessible only by a user with
// the admin role

Route::group(['middleware' => 'demo'], function() {
    Route::apiResources(
        [
            'playlists' => PlaylistController::class,
            'podcast-genres' => PodcastGenreController::class,
            'artists' => ArtistController::class,
            'albums' => AlbumController::class,
            'users' => UserController::class,
            'genres' => GenreController::class,
            'payouts' => PayoutController::class,
            'payout-methods' => PayoutMethodController::class,
            'pages' => PageController::class,
            'subscriptions' => SubscriptionController::class,
            'roles' => RoleController::class,
            'plans' => PlanController::class,
            'campaigns' => CampaignController::class,
            'sections' => SectionController::class,
            'radio-stations' => RadioStationController::class,
        ],
        [
            'except' => 'index'
        ]
    );
});




Route::apiResources(
    [
        'playlists' => PlaylistController::class,
        'podcast-genres' => PodcastGenreController::class,
        'artists' => ArtistController::class,
        'users' => UserController::class,
        'genres' => GenreController::class,
        'pages' => PageController::class,
        'subscriptions' => SubscriptionController::class,
        'payouts' => PayoutController::class,
        'payout-methods' => PayoutMethodController::class,
        'sales' => SaleController::class,
        'sections' => SectionController::class,
        'radio-stations' => RadioStationController::class,
        'albums' => AlbumController::class,
        'songs' => SongController::class,
        'videos' => VideoController::class,
        'podcasts' => PodcastController::class,
        'roles' => RoleController::class,
        'episodes' => EpisodeController::class,
        'plans' => PlanController::class,
        'campaigns' => CampaignController::class,
    ],
    [
        'only' => ['index']
    ]
);

Route::post('/add-tracks-to-playlists', 'PlaylistController@addTracksToPlaylist');
Route::post('/delete-tracks', 'SongController@deleteTracks');
Route::post('/block/{user}', 'UserController@block');
Route::post('/unblock/{user}', 'UserController@unblock');


Route::group(['prefix' => 'locales'], function () {
    Route::get('/{locale}/messages', 'LanguageController@messages');
    Route::get('/language', 'LanguageController@index');
    Route::group(['middleware' => 'demo'], function() {
        Route::post('/activate-language/{locale}', 'LanguageController@activate');
        Route::post('/language', 'LanguageController@store');
        Route::post('/language/{id}/update', 'LanguageController@update');
        Route::delete('/language/{id}', 'LanguageController@destroy');
        Route::post('/save-messages/{locale}', 'LanguageController@saveMessages');
    });
});


Route::get('/url', 'AdminController@getUrl');
Route::get('/pages-infos', 'PageController@indexInfosOnly');
Route::get('/roles', 'RoleController@index');
Route::get('/settings', 'SettingController@index');
Route::get('/themes', 'SettingController@themes');
Route::get('/notifications', 'AdminController@notifications');
Route::get('/plays/{duration}', 'AnalyticsController@getPlays');
Route::get('/sales/{duration}', 'AnalyticsController@getSales');
Route::get('/generate-sitemap', 'AdminController@generateSitemap');
Route::get('/analytics', 'AnalyticsController@index');
Route::get('/album/export', 'AlbumController@exportCSV');
Route::get('/radiostation/export', 'RadioStationController@exportCSV');
Route::get('/songs/export', 'SongController@exportCSV');
Route::get('/video/export', 'VideoController@exportCSV');
Route::get('/playlist/export', 'PlaylistController@exportCSV');
Route::get('/podcasts/export', 'PodcastController@exportCSV');
Route::get('/podcast-genres-export', 'PodcastGenreController@exportCSV');
Route::get('/genres-export', 'GenreController@exportCSV');


Route::group(['middleware' => 'demo'], function() {
    Route::post('/upload', 'UploadController@store')->name('admin.upload');
    Route::post('/contact', 'AdminController@contact');
    Route::post('/mark-payout', 'PayoutController@mark');
    Route::post('/save-general-settings', 'SettingController@saveAppearanceGeneralSettings');
    Route::post('/reset-roles', 'RoleController@resetRoles');
    Route::post('/save-roles', 'RoleController@saveRoles');
    Route::post('/save-settings', 'SettingController@saveSettings');
    Route::post('/save-player-settings', 'SettingController@savePlayerSettings');
    Route::post('/save-providers', 'SettingController@saveProviders');
    Route::post('/save-analytics-settings', 'SettingController@saveAnalyticsSettings');
    Route::post('/reset-settings', 'SettingController@resetSettings');
    Route::post('/save-ads', 'SettingController@saveAds');
    Route::post('/roles/sync', 'RoleController@sync');
    Route::post('/save-mail-settings', 'SettingController@saveMailSettings');
    Route::post('/save-billing-settings', 'SettingController@saveBillingSettings');
    Route::post('/save-storage-settings', 'SettingController@saveStorageSettings');
    Route::post('/upload-section-background', 'SettingController@uploadSectionBackground');
    Route::post('/save-landing-page-settings', 'SettingController@saveLandingPageSettings');
    Route::post('/save-navigation-items', 'NavigationItemController@saveNavigationItems');
    Route::post('/reset-navigation-items', 'NavigationItemController@resetNavigationItems');
    Route::post('/update-explore-sections', 'SectionController@updateExploreSections');
    Route::post('/save-themes', 'SettingController@saveThemes');
    Route::post('/approve-user-artist-request', 'RoleController@acceptArtistAccountRequest');
    Route::post('/reject-user-artist-request', 'RoleController@rejectArtistAccountRequest');
    Route::group(['middleware' => 'scope:do_anything'], function () {
        Route::post('/make-admin', 'AdminController@grantUserAdmin');
        Route::post('/take-admin', 'AdminController@RevokeUserAdmin');
    });

    Route::post('/additional-pay', 'AdditionalPayController@store');
    Route::post('/additional-pay/{id}', 'AdditionalPayController@destroy');
});
