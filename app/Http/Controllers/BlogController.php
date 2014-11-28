<?php namespace App\Http\Controllers;

use App\Gestion\BlogGestion;
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\SearchRequest;
use Illuminate\Contracts\Auth\Guard;
use App\Gestion\UserGestion;
use App\Services\Pagination;
use App\Services\Medias;

/**
 * @Resource("blog")
 * @Middleware("redac", except={"indexFront","show","tag","search"}) 
 */
class BlogController extends Controller {

	/**
	 * The BlogGestion instance.
	 *
	 * @var App\Gestion\BlogGestion
	 */
	protected $blog_gestion;

    /**
     * The UserGestion instance.
     *
     * @var App\Gestion\UserGestion
     */
    protected $user_gestion;

    /**
	 * The pagination number.
	 *
	 * @var App\Gestion\UserGestion
	 */
	protected $nbrPages;

	/**
	 * Create a new BlogController instance.
	 *
	 * @param  App\Gestion\BlogGestion $blog_gestion
     * @param  App\Gestion\UserGestion $user_gestion
	 * @return void
	 */
	public function __construct(
		BlogGestion $blog_gestion,
    UserGestion $user_gestion)
	{
		$user_gestion->getStatut();
    $this->user_gestion = $user_gestion;
		$this->blog_gestion = $blog_gestion;
		$this->nbrPages = 2;
	}	

	/**
	 * Display a listing of the resource.
	 *
	 * @Get("articles")
	 *
	 * @return Response
	 */
	public function indexFront()
	{
    $posts = $this->blog_gestion->indexFront($this->nbrPages);
    $links = str_replace('/?', '?', $posts->render());
		return view('front.blog.index', compact('posts', 'links'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  Illuminate\Contracts\Auth\Guard $auth
	 * @return Response
	 */
	public function index(
		Guard $auth)
	{
		$statut = $this->user_gestion->getStatut();
		$posts = $this->blog_gestion->index(10, $statut == 'admin' ? null : $auth->user()->id);
		$links = Pagination::makeLengthAware($posts, $this->blog_gestion->count(false, $statut == 'admin' ? null : $auth->user()->id), 10);
		return view('back.blog.index', compact('posts', 'links'));
	}

	/**
	 * Display a listing of the resource.
	 * @Get("blog/order")
	 *
	 * @param  Illuminate\Http\Request $request
	 * @param  Illuminate\Contracts\Auth\Guard $auth
	 * @return Response
	 */
	public function indexOrder(
		Request $request, 
		Guard $auth)
	{
		$statut = $this->user_gestion->getStatut();
		$posts = $this->blog_gestion->index(10, $statut == 'admin' ? null : $auth->user()->id, $request->get('name'), $request->get('sens'));
		$links = Pagination::makeLengthAware($posts, $this->blog_gestion->count(false, $statut == 'admin' ? null : $auth->user()->id), 10);
		return response()->json([
			'view' => view('back.blog.table', compact('statut', 'posts'))->render(), 
			'links' => $links
		]);		
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  App\Gestion\UserGestion $user_gestion
	 * @return Response
	 */
	public function create(
		UserGestion $user_gestion)
	{
		$url = Medias::getUrl($user_gestion);
		return view('back.blog.create')->with(compact('url'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  App\Http\Requests\PostCreateRequest $postrequest
	 * @param  Illuminate\Http\Request $request
	 * @param  Illuminate\Contracts\Auth\Guard $auth
	 * @return Response
	 */
	public function store(
		PostRequest $postrequest,
		Request $request,
		Guard $auth)
	{
		$this->blog_gestion->store($request->all(), $auth->user()->id);
		return redirect('blog')->with('ok', trans('back/blog.stored'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Illuminate\Contracts\Auth\Guard $auth	 
	 * @param  int  $id
	 * @return Response
	 */
	public function show(
		Guard $auth, 
		$id)
	{
		$user = $auth->user();
		return view('front.blog.show',  array_merge($this->blog_gestion->show($id), compact('user')));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  App\Gestion\UserGestion $user_gestion
	 * @param  int  $id
	 * @return Response
	 */
	public function edit(
		UserGestion $user_gestion, 
		$id)
	{
		$url = Medias::getUrl($user_gestion);
		return view('back.blog.edit',  array_merge($this->blog_gestion->edit($id), compact('url')));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  App\Http\Requests\PostUpdateRequest $blogrequest
	 * @param  Illuminate\Http\Request $request
	 * @param  int  $id
	 * @return Response
	 */
	public function update(
		PostRequest $postrequest,
		Request $request,
		$id)
	{
		$this->blog_gestion->update($request->all(), $id);
		return redirect('blog')->with('ok', trans('back/blog.updated'));		
	}

	/**
	 * Update "vu" for the specified resource in storage.
	 *
	 * @Put("postvu/{id}")
	 *
	 * @param  Illuminate\Http\Request $request
	 * @param  int  $id
	 * @return Response
	 */
	public function updateVu(
		Request $request, 
		$id)
	{
		$this->blog_gestion->updateVu($request->all(), $id);
		return response()->json(['statut' => 'ok']);
	}

	/**
	 * Update "actif" for the specified resource in storage.
	 *
	 * @Put("postactif/{id}")
	 *
	 * @param  Illuminate\Http\Request $request
	 * @param  int  $id
	 * @return Response
	 */
	public function updateActif(
		Request $request, 
		$id)
	{
		$this->blog_gestion->updateActif($request->all(), $id);
		return response()->json(['statut' => 'ok']);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Illuminate\Http\Request $request
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(
		Request $request,	
		$id)
	{
		$this->blog_gestion->destroy($id);
		return redirect('blog')->with('ok', trans('back/blog.destroyed'));		
	}

	/**
	 * @Get("blog/tag")
	 *
	 * @param  Illuminate\Http\Request $request
	 * @return Response
	 */
	public function tag(Request $request)
	{
		$tag = $request->get('tag');
		$posts = $this->blog_gestion->indexTag($this->nbrPages, $tag);
		$links = str_replace('/?', '?', $posts->appends(compact('tag'))->render());
		$info = trans('front/blog.info-tag') . '<strong>' . $this->blog_gestion->getTagById($tag) . '</strong>';
		return view('front.blog.index', compact('posts', 'links', 'info'));
	}

	/**
	 * Find search in blog
	 *
	 * @Get("blog/search")
	 *
	 * @param  App\Http\Requests\SearchRequest $searchrequest
	 * @param  Illuminate\Http\Request $request
	 * @return Response
	 */
	public function search(
		SearchRequest $searchrequest,
		Request $request)
	{
		$search = $request->get('search');
		$posts = $this->blog_gestion->search($this->nbrPages, $search);
		$links = str_replace('/?', '?', $posts->appends(compact('search'))->render());
		$info = trans('front/blog.info-search') . '<strong>' . $search . '</strong>';
		return view('front.blog.index', compact('posts', 'links', 'info'));
	}

}
