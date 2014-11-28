<?php namespace App\Http\Controllers;

use App\Gestion\UserGestion;
use App\Gestion\RoleGestion;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Requests\RoleRequest;
use Illuminate\Http\Request;
use App\Services\Pagination;

/**
 * @Resource("user")
 * @Middleware("admin")
 */
class UserController extends Controller {

	/**
	 * The UserGestion instance.
	 *
	 * @var App\Gestion\UserGestion
	 */
	protected $user_gestion;

	/**
	 * The RoleGestion instance.
	 *
	 * @var App\Gestion\RoleGestion
	 */	
	protected $role_gestion;

	/**
	 * Create a new UserController instance.
	 *
	 * @param  App\Gestion\UserGestion $user_gestion
	 * @param  App\Gestion\RoleGestion $role_gestion
	 * @return void
	 */
	public function __construct(
		UserGestion $user_gestion,
		RoleGestion $role_gestion)
	{
		$this->user_gestion = $user_gestion;
		$this->role_gestion = $role_gestion;
		$user_gestion->getStatut();
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return $this->indexGo('total');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @Get("user/sort/{role}")
	 *
	 * @return Response
	 */
	public function indexSort($role)
	{
		return $this->indexGo($role, true);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  string  $role
	 * @param  bool    $ajax
	 * @return Response
	 */
	private function indexGo($role, $ajax = false)
	{
		$counts = [
			'admin' => $this->user_gestion->count('admin'),
			'redac' => $this->user_gestion->count('redac'),
			'user' => $this->user_gestion->count('user')
		];
		$counts['total'] = array_sum($counts);
		$users = $this->user_gestion->index(4, $role); 
		$links = Pagination::makeLengthAware($users, $counts[$role], 4);
		$roles = $this->role_gestion->all();
		if($ajax)
		{
			return response()->json([
				'view' => view('back.users.table', compact('users', 'links', 'counts', 'roles'))->render(), 
				'links' => $links
			]);			
		}
		return view('back.users.index', compact('users', 'links', 'counts', 'roles'));		
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		return view('back.users.create', $this->user_gestion->create());
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  App\requests\UserCreateRequest $usercreaterequest
	 * @param  Illuminate\Http\Request $request
	 *
	 * @return Response
	 */
	public function store(
		UserCreateRequest $usercreaterequest,
		Request $request)
	{
		$this->user_gestion->store($request->all());
		return redirect('user')->with('ok', trans('back/users.created'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		return view('back.users.show',  $this->user_gestion->show($id));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		return view('back.users.edit',  $this->user_gestion->edit($id));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  App\requests\UserUpdateRequest $userupdaterequest
	 * @param  Illuminate\Http\Request $request
	 * @param  int  $id
	 * @return Response
	 */
	public function update(
		UserUpdateRequest $userupdaterequest,
		Request $request,
		$id)
	{
		$this->user_gestion->update($request->all(), $id);
		return redirect('user')->with('ok', trans('back/users.updated'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @Put("uservu/{id}")
	 *
	 * @param  Illuminate\Http\Request $request
	 * @param  int  $id
	 * @return Response
	 */
	public function updateVu(
		Request $request, 
		$id)
	{
		$this->user_gestion->update($request->all(), $id);
		return response()->json(['statut' => 'ok']);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$this->user_gestion->destroy($id);
		return redirect('user')->with('ok', trans('back/users.destroyed'));
	}

	/**
	 * Display the roles form
	 *
	 * @Get("user/roles")
	 *
	 * @return Response
	 */
	public function getRoles()
	{
		$roles = $this->role_gestion->all();
		return view('back.users.roles', compact('roles'));
	}

	/**
	 * Update roles
	 *
	 * @Post("user/roles")
	 *
	 * @param  App\requests\RoleRequest $rolerequest
	 * @param  Illuminate\Http\Request $request
	 * @return Response
	 */
	public function postRoles(
		RoleRequest $rolerequest,
		Request $request)
	{
		$this->role_gestion->update($request->except('_token'));
		return redirect('user/roles')->with('ok', trans('back/roles.ok'));
	}

}
