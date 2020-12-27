<?php

namespace App\Http\Controllers;

use App\Company;
use App\VacationPackage;
use App\VacationPackageCategory;
use Hash;
use Carbon\Carbon;
use App\User;
use App\Image;
use App\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * @param $slug
     * @param Request $request
     * @return View
     */
    public function page($slug, Request $request): View
    {
        $company = Company::whereSlug($slug)->first();
        if (!$company) {
            abort(404);
        }

        $company->loadMissing('user');
        $company->loadMissing('vacationPackages');
        $routeName = $request->route()->getName();

        $items = [
            [
                'title' => 'Tutvustus',
                'route' => route('company.page', ['slug' => $company->slug]),
                'active' => $routeName !== 'company.page' ? $routeName === 'company.page' : '#',
                'count' => null
            ],
            [
                'title' => 'Pakkumised',
                'route' => $routeName !== 'company.packages' ? route('company.packages', ['slug' => $company->slug]) : '#',
                'active' => $routeName === 'company.packages',
                'count' => count($company->vacationPackages)
            ]
        ];

        return view('pages.company.page', [
            'company' => $company,
            'user' => $company->user,
            'items' => $items
        ]);
    }

    /**
     * @param string $slug
     * @param Request $request
     * @return View
     */
    public function packages(string $slug, Request $request): View
    {
        $company = Company::whereSlug($slug)->first();
        if (!$company) {
            abort(404);
        }

        $company->loadMissing('user');
        $company->loadMissing('activeVacationPackages');
        $items = [
            [
                'title' => 'Tutvustus',
                'route' => route('company.page', ['slug' => $company->slug]),
                'active' => false,
                'count' => null
            ],
            [
                'title' => 'Pakkumised',
                'route' => '#',
                'active' => true,
                'count' => count($company->activeVacationPackages)
            ]
        ];

        return view('pages.company.packages', [
            'company' => $company,
            'user' => $company->user,
            'packages' => $company->activeVacationPackages,
            'items' => $items
        ]);
    }

    /**
     * @param Company $company
     * @param Request $request
     * @return View
     */
    public function profile(Company $company, Request $request)
    {
        $company->loadMissing('user');
        $company->loadMissing('vacationPackages');
        $routeName = $request->route()->getName();

        $items = [
            [
                'title' => 'Pakkumised',
                'route' => route('company.profile', ['company' => $company]),
                'active' => $routeName !== 'company.profile' ? $routeName === 'company.profile' : '#',
                'count' => count($company->vacationPackages)
            ],
            [
                'title' => 'Minu info',
                'route' => $routeName !== 'company.edit_profile' ? route('company.edit_profile', ['company' => $company]) : '#',
                'active' => $routeName === 'company.edit_profile',
                'count' => null
            ]
        ];

        return view('pages.company.profile', [
            'company' => $company,
            'user' => $company->user,
            'items' => $items
        ]);
    }

    /**
     * @param Company $company
     * @param Request $request
     * @return View
     */
    public function editProfile(Company $company, Request $request)
    {
        $company->loadMissing('user');
        $routeName = $request->route()->getName();

        $items = [
            [
                'title' => 'Pakkumised',
                'route' => route('company.profile', ['company' => $company]),
                'active' => $routeName !== 'company.profile' ? $routeName === 'company.profile' : '#',
                'count' => count($company->vacationPackages)
            ],
            [
                'title' => 'Minu info',
                'route' => $routeName !== 'company.edit_profile' ? route('company.edit_profile', ['company' => $company]) : '#',
                'active' => $routeName === 'company.edit_profile',
                'count' => null
            ]
        ];

        return view('pages.company.edit-profile', [
            'company' => $company,
            'user' => $company->user,
            'items' => $items
        ]);
    }

    /**
     * @param Company $company
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function updateProfile(Company $company, Request $request)
    {
        $user = $company->user;
        $maxFileSize = config('site.maxfilesize') * 1024;

        $rules = [
            'company_name' => 'required|max:64|unique:companies,name,' . $company->id,
            'email' => 'required|unique:users,email,' . $user->id,
            'password' => 'sometimes|confirmed|min:6',
            'password_confirmation' => 'required_with:password|same:password',
            'description' => 'min:2',
            'facebook' => 'url',
            'homepage' => 'url',
            'file' => "image|max:$maxFileSize"
        ];

        $this->validate($request, $rules);

        $data = [
            'email' => $request->email,
            'description' => $request->description,
            'contact_facebook' => $request->facebook,
            'contact_homepage' => $request->homepage
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $company->slug = null;
        $company->update([
            'name' => $request->company_name
        ]);

        if ($request->hasFile('logo')) {
            $filename =
                'picture-' .
                $user->id .
                '.' .
                $request
                    ->file('logo')
                    ->getClientOriginalExtension();

            $filename = Image::storeImageFile($request->file('logo'), $filename);

            $user->images()->delete();
            $user->images()->create(['filename' => $filename]);
        }

        return redirect()
            ->route('company.page', ['slug' => $company->slug])
            ->with('info', trans('user.update.info'));
    }

    /**
     * @param Company $company
     * @return View
     */
    public function addPackage(Company $company)
    {
        $company->loadMissing('user');
        $categories = VacationPackageCategory::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('pages.company.vacation-package-form', [
            'company' => $company,
            'user' => $company->user,
            'package' => null,
            'categoryOptions' => $categories,
            'submitRoute' => route('company.store_package', $company),
            'title' => 'Lisa uus pakkumine'
        ]);
    }

    /**
     * @param Company $company
     * @param Request $request
     * @return JsonResponse
     */
    public function storePackage(Company $company, Request $request)
    {
        $rules = [
            'name' => 'required',
            'link' => 'required|url',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'price' => 'required|numeric',
            'description' => 'required',
            'category' => 'required|array|min:1'
        ];

        $validator = Validator::make($request->post(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->getMessageBag()->all(),
                'keys' => $validator->errors()->keys(),
            ], 422);
        }

        $package = new VacationPackage();
        $package->company_id = $company->id;
        $package->name = $request->post('name');
        $package->start_date = $request->post('startDate');
        $package->end_date = $request->post('endDate');
        $package->price = $request->post('price');
        $package->description = $request->post('description');
        $package->link = $request->post('link');
        $package->save();

        $package->vacationPackageCategories()->attach(request()->category);

        Session::flash(
            'info', trans('Uus pakkumine loodud')
        );

        return response()->json([
            'success' => true,
            'route' => route('company.profile', ['company' => $company])
        ]);
    }

    /**
     * @param Company $company
     * @param VacationPackage $package
     * @return View
     */
    public function editPackage(Company $company, VacationPackage $package)
    {
        $company->loadMissing('user');
        $categories = VacationPackageCategory::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('pages.company.vacation-package-form', [
            'company' => $company,
            'user' => $company->user,
            'package' => $package,
            'categoryOptions' => $categories,
            'submitRoute' => route('company.update_package', [$company, $package]),
            'title' => 'Muuda pakkumist'
        ]);
    }

    /**
     * @param Company $company
     * @param VacationPackage $package
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePackage(Company $company, VacationPackage $package, Request $request)
    {
        $rules = [
            'name' => 'required',
            'link' => 'required|url',
            'startDate' => 'required|date',
            'endDate' => 'required|date',
            'price' => 'required|numeric',
            'description' => 'required',
            'category' => 'required|array|min:1'
        ];

        $validator = Validator::make($request->post(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()->getMessageBag()->all(),
                'keys' => $validator->errors()->keys(),
            ], 422);
        }

        $package->slug = null;
        $package->name = $request->post('name');
        $package->start_date = $request->post('startDate');
        $package->end_date = $request->post('endDate');
        $package->price = $request->post('price');
        $package->description = $request->post('description');
        $package->link = $request->post('link');
        $package->save();

        $package->vacationPackageCategories()->sync(request()->category);

        Session::flash(
            'info', trans('Pakkumine salvestatud')
        );

        return response()->json([
            'success' => true,
            'route' => route('company.profile', ['company' => $company])
        ]);
    }

    /**
     * @param Company $company
     * @param VacationPackage $package
     * @return RedirectResponse
     */
    public function setPackageActive(Company $company, VacationPackage $package)
    {
        if ($package->active) {
            $package->active = false;
            $package->save();
        } else {
            //todo: check subscription
            $package->active = true;
            $package->save();
        }

        return back()->with(
            'info',
            trans('general.notification.saved')
        );
    }

    /**
     * @param Company $company
     * @return View
     */
    /*public function editProfile(Company $company)
    {
        $company->loadMissing('user');
        return view('pages.company.edit-profile-OLD', [
            'user' => $company->user,
            'company' => $company
        ]);
    }*/



    public function index()
    {
        $loggedUser = request()->user();

        $offers = $loggedUser
            ->offers()
            ->orderBy('start_at')
            ->with(['user:id,name', 'startDestinations', 'endDestinations'])
            ->get();

        return layout('Full')
            ->withHeadRobots('noindex')
            ->withTransparency(true)
            ->withTitle(trans('offer.index'))
            ->withItems(
                collect()
                    ->push(
                        component('Section')
                            ->withPadding(2)
                            ->withTag('header')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('NavbarLight')))
                    )
                    ->push(
                        component('Section')
                            ->withBackground('blue')
                            ->withPadding(2)
                            ->withGap(2)
                            ->withWidth(styles('tablet-width'))
                            ->withItems(
                                collect()
                                    ->push(
                                        component('Title')
                                            ->is('white')
                                            ->is('large')
                                            ->with('title', trans('company.index.title'))
                                    )
                                    ->push(region('CompanyOffersButtons', $loggedUser))
                                    ->spacer(4)
                                    ->push(region('CompanyOffers', $offers))
                            )
                    )
                    ->push(
                        component('Section')
                            ->withTag('footer')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('FooterLight', '')))
                    )
            )
            ->render();
    }

    public function adminIndex()
    {
        $companies = User::whereCompany(true)->get();

        $offers = Offer::orderBy('start_at')
            ->with(['user:id,name', 'startDestinations', 'endDestinations'])
            ->take(100)
            ->get();

        return layout('Full')
            ->withHeadRobots('noindex')
            ->withTransparency(true)
            ->withTitle(trans('offer.index'))
            ->withItems(
                collect()
                    ->push(
                        component('Section')
                            ->withPadding(2)
                            ->withTag('header')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('NavbarLight')))
                    )
                    ->push(
                        component('Section')
                            ->withBackground('blue')
                            ->withPadding(2)
                            ->withGap(2)
                            ->withWidth(styles('tablet-width'))
                            ->withItems(
                                collect()
                                    ->push(
                                        component('Title')
                                            ->is('white')
                                            ->is('large')
                                            ->with('title', trans('company.admin.index.title'))
                                    )
                                    ->push(
                                        component('Button')
                                            ->is('narrow')
                                            ->with('title', trans('company.create'))
                                            ->with(
                                                'route',
                                                route('company.create', ['redirect' => 'company.admin.index'])
                                            )
                                    )
                                    ->spacer(2)
                                    ->push(region('CompanyAdminTable', $companies))
                                    ->spacer(4)
                                    ->push(
                                        component('Title')
                                            ->is('large')
                                            ->is('white')
                                            ->with('title', trans('company.admin.index.offer'))
                                    )
                                    ->spacer(0.5)
                                    ->push(region('CompanyOffersAdmin', $offers))
                            )
                    )
                    ->push(
                        component('Section')
                            ->withTag('footer')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('FooterLight', '')))
                    )
            )
            ->render();
    }

    public function show($id)
    {
        $user = User::whereCompany(true)->findOrFail($id);
        return redirect()->route('offer.index', ['user_id' => $user->id]);
    }

    public function create()
    {
        $loggedUser = request()->user();

        return layout('Full')
            // @LAUNCH Remove
            ->withHeadRobots('noindex')
            ->withTransparency(true)
            ->withTitle(trans('offer.index'))
            ->withItems(
                collect()
                    ->push(
                        component('Section')
                            ->withPadding(2)
                            ->withTag('header')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('NavbarLight')))
                    )
                    ->push(
                        component('Section')
                            ->withBackground('blue')
                            ->withPadding(2)
                            ->withWidth(styles('tablet-width'))
                            ->withItems(
                                component('Title')
                                    ->is('white')
                                    ->is('large')
                                    ->withTitle(trans('company.create.title'))
                            )
                    )
                    ->push(
                        component('Section')
                            ->withBackground('blue')
                            ->withInnerBackground('white')
                            ->withInnerPadding(2)
                            ->withWidth(styles('tablet-width'))
                            ->withItems(
                                component('Form2')
                                    ->with('route', route('company.store'))
                                    ->with('files', true)
                                    ->with(
                                        'fields',
                                        collect()
                                            ->push(
                                                component('Title')
                                                    ->is('small')
                                                    ->is('blue')
                                                    ->with('title', trans('company.edit.credentials'))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.name.title'))
                                                    ->with('name', 'name')
                                                    ->with('value', old('name'))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.company_name.title'))
                                                    ->with('name', 'company_name')
                                                    ->with('value', old('company_name'))
                                            )

                                            ->push(
                                                component('FormPassword')
                                                    ->is('large')
                                                    ->with('title', trans('company.create.password.title'))
                                                    ->with('name', 'password')
                                                    ->with('value', '')
                                            )
                                            ->push(
                                                component('FormPassword')
                                                    ->is('large')
                                                    ->with('title', trans('company.create.password_confirmation.title'))
                                                    ->with('name', 'password_confirmation')
                                                    ->with('value', '')
                                            )
                                            ->push(
                                                component('Title')
                                                    ->is('small')
                                                    ->is('blue')
                                                    ->with('title', trans('company.edit.about'))
                                            )
                                            ->push(component('FormUpload')->with('name', 'file'))
                                            ->push(
                                                component('FormTextarea')
                                                    ->with('rows', 4)
                                                    ->with('title', trans('company.edit.description.title'))
                                                    ->with('name', 'description')
                                                    ->with('value', old('description'))
                                            )
                                            ->push(
                                                component('Title')
                                                    ->is('small')
                                                    ->is('blue')
                                                    ->with('title', trans('company.edit.contacts.title'))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.email.title'))
                                                    ->with('name', 'email')
                                                    ->with('value', old('email'))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->with('title', trans('company.edit.homepage.title'))
                                                    ->with('name', 'contact_homepage')
                                                    ->with('value', old('contact_homepage'))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->with('title', trans('company.edit.facebook.title'))
                                                    ->with('name', 'contact_facebook')
                                                    ->with('value', old('contact_facebook'))
                                            )
                                            ->push(
                                                component('FormButton')
                                                    ->is('wide')
                                                    ->is('large')
                                                    ->with('title', trans('company.create.submit'))
                                            )
                                    )
                            )
                    )
                    ->push(
                        component('Section')
                            ->withTag('footer')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('FooterLight', '')))
                    )
            )
            ->render();
    }

    public function store()
    {
        $maxfilesize = config('site.maxfilesize') * 1024;

        $rules = [
            'name' => 'required|unique:users,name',
            'company_name' => 'required|unique:users,real_name',
            'email' => 'required|unique:users,email',
            'password' => 'required|sometimes|confirmed|min:6',
            'password_confirmation' => 'required_with:password|same:password',
            'description' => 'min:2',
            'contact_facebook' => 'url',
            'contact_homepage' => 'url',
            'file' => "image|max:$maxfilesize"
        ];

        $this->validate(request(), $rules);

        $user = User::create([
            'name' => request()->name,
            'email' => request()->email,
            'password' => Hash::make(request()->password),
            'real_name' => request()->company_name,
            'real_name_show' => 1,
            'notify_message' => 0,
            'notify_follow' => 0,
            'description' => request()->description,
            'contact_facebook' => request()->contact_facebook,
            'contact_instagram' => '',
            'contact_twitter' => '',
            'contact_homepage' => request()->contact_homepage,
            'active_at' => Carbon::now(),
            'verified' => 1,
            'company' => true
        ]);

        if (request()->hasFile('file')) {
            $filename =
                'picture-' .
                $user->id .
                '.' .
                request()
                    ->file('file')
                    ->getClientOriginalExtension();

            $filename = Image::storeImageFile(request()->file('file'), $filename);

            $user->images()->delete();
            $user->images()->create(['filename' => $filename]);
        }

        return redirect()
            ->route('company.index')
            ->with('info', trans('company.create.info'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return layout('Full')
            ->withHeadRobots('noindex')
            ->withTransparency(true)
            ->withTitle(trans('offer.index'))
            ->withItems(
                collect()
                    ->push(
                        component('Section')
                            ->withPadding(2)
                            ->withTag('header')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('NavbarLight')))
                    )
                    ->push(
                        component('Section')
                            ->withBackground('blue')
                            ->withPadding(2)
                            ->withWidth(styles('tablet-width'))
                            ->withItems(
                                component('Title')
                                    ->is('white')
                                    ->is('large')
                                    ->withTitle(trans('company.edit.title'))
                            )
                    )
                    ->push(
                        component('Section')
                            ->withBackground('blue')
                            ->withInnerBackground('white')
                            ->withInnerPadding(2)
                            ->withWidth(styles('tablet-width'))
                            ->withItems(
                                component('Form2')
                                    ->with('route', route('company.update', [$user]))
                                    ->with('method', 'PUT')
                                    ->with('files', true)
                                    ->with(
                                        'fields',
                                        collect()
                                            ->push(
                                                component('Title')
                                                    ->is('small')
                                                    ->is('blue')
                                                    ->with('title', trans('company.edit.credentials'))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.name.title'))
                                                    ->with('name', 'name')
                                                    ->with('value', old('name', $user->name))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.company_name.title'))
                                                    ->with('name', 'company_name')
                                                    ->with('value', old('company_name', $user->real_name))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.email.title'))
                                                    ->with('name', 'email')
                                                    ->with('value', old('email', $user->email))
                                            )
                                            ->push(
                                                component('FormPassword')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.password.title'))
                                                    ->with('name', 'password')
                                                    ->with('value', '')
                                            )
                                            ->push(
                                                component('FormPassword')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.password_confirmation.title'))
                                                    ->with('name', 'password_confirmation')
                                                    ->with('value', '')
                                            )
                                            ->push(
                                                component('Title')
                                                    ->is('small')
                                                    ->is('blue')
                                                    ->with('title', trans('company.edit.about'))
                                            )
                                            ->push(component('FormUpload')->with('name', 'file'))
                                            ->push(
                                                component('FormTextarea')
                                                    ->with('rows', 4)
                                                    ->with('title', trans('company.edit.description.title'))
                                                    ->with('name', 'description')
                                                    ->with('value', old('description', $user->description))
                                            )
                                            ->push(
                                                component('Title')
                                                    ->is('small')
                                                    ->is('blue')
                                                    ->with('title', trans('company.edit.contacts.title'))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->with('title', trans('company.edit.homepage.title'))
                                                    ->with('name', 'contact_homepage')
                                                    ->with('value', old('contact_homepage', $user->contact_homepage))
                                            )
                                            ->push(
                                                component('FormTextfield')
                                                    ->with('title', trans('company.edit.facebook.title'))
                                                    ->with('name', 'contact_facebook')
                                                    ->with('value', old('contact_facebook', $user->contact_facebook))
                                            )
                                            ->pushWhen(
                                                request()->has('redirect'),
                                                component('FormHidden')
                                                    ->with('name', 'redirect')
                                                    ->with('value', request()->redirect)
                                            )
                                            ->push(
                                                component('FormButton')
                                                    ->is('wide')
                                                    ->is('large')
                                                    ->with('title', trans('company.edit.submit'))
                                            )
                                    )
                            )
                    )
                    ->push(
                        component('Section')
                            ->withTag('footer')
                            ->withBackground('blue')
                            ->withItems(collect()->push(region('FooterLight', '')))
                    )
            )
            ->render();
    }

    public function update($id)
    {
        $user = User::findorFail($id);
        $maxfilesize = config('site.maxfilesize') * 1024;

        $rules = [
            'name' => 'required|unique:users,name,' . $user->id,
            'email' => 'required|unique:users,email,' . $user->id,
            'password' => 'sometimes|confirmed|min:6',
            'password_confirmation' => 'required_with:password|same:password',
            'description' => 'min:2',
            'contact_facebook' => 'url',
            'contact_homepage' => 'url',
            'file' => "image|max:$maxfilesize"
        ];

        $this->validate(request(), $rules);

        $user->update([
            'name' => request()->name,
            'email' => request()->email,
            'password' => Hash::make(request()->password),
            'real_name' => request()->company_name,
            'real_name_show' => request()->real_name_show ? 0 : 1,
            'notify_message' => request()->notify_message ? 1 : 0,
            'notify_follow' => request()->notify_follow ? 1 : 0,
            'description' => request()->description,
            'contact_facebook' => request()->contact_facebook,
            'contact_instagram' => '',
            'contact_twitter' => '',
            'contact_homepage' => request()->contact_homepage
        ]);

        if (request()->hasFile('file')) {
            $filename =
                'picture-' .
                $user->id .
                '.' .
                request()
                    ->file('file')
                    ->getClientOriginalExtension();

            $filename = Image::storeImageFile(request()->file('file'), $filename);

            $user->images()->delete();
            $user->images()->create(['filename' => $filename]);
        }

        return redirect()
            ->route(request()->has('redirect') ? request()->redirect : 'company.index')
            ->with('info', trans('company.edit.info'));
    }
}
