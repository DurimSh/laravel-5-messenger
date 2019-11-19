<?php

namespace Lexx\ChatMessenger\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Eloquent
{
	use SoftDeletes;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'messages';

	/**
	 * The relationships that should be touched on save.
	 *
	 * @var array
	 */
	protected $touches = ['thread'];

	/**
	 * The attributes that can be set with Mass Assignment.
	 *
	 * @var array
	 */
	protected $fillable = ['thread_id', 'user_id', 'company_id', 'body'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];


	protected $appends = ['company_name', 'company_logo', 'person_name', 'person_avatar'];

	/**
	 * {@inheritDoc}
	 */
	public function __construct(array $attributes = [])
	{
		$this->table = Models::table('messages');

		parent::__construct($attributes);
	}


	/**
	 * @return mixed|string
	 */
	public function getCompanyLogoAttribute()
	{
		if ($this->company_id) {
			$company = Company::find($this->company_id);
			if (!empty($company)) {
				$logo = $company->logo();
				if ($logo->count() > 0) {
					return $logo->first()->link;
				}
			}
		}
		return null;
	}

	/**
	 * @return array|mixed
	 */
	public function getCompanyNameAttribute()
	{
		if ($this->company_id) {
			$company = Company::find($this->company_id);
			if ($company) {
				return $company->name;
			}
		}

		return null;
	}

	/**
	 * @return mixed|string
	 */
	public function getPersonAvatarAttribute()
	{
		if (!$this->company_id) {
			$user = User::find($this->user_id);
			if (!empty($user)) {
				$avatar = $user->avatar();
				if ($avatar->count() > 0) {
					return $avatar->link;
				}
			}
		}
		return null;
	}

	/**
	 * @return array|mixed
	 */
	public function getPersonNameAttribute()
	{
		if (!$this->company_id) {
			$user = User::find($this->user_id);
			if ($user) {
				return $user->name;
			}
		}

		return null;
	}

	/**
	 * Thread relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 *
	 * @codeCoverageIgnore
	 */
	public function thread()
	{
		return $this->belongsTo(Models::classname(Thread::class), 'thread_id', 'id');
	}

	/**
	 * User relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 *
	 * @codeCoverageIgnore
	 */
	public function user()
	{
		return $this->belongsTo(Models::user(), 'user_id');
	}

	/**
	 * Participants relationship.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 *
	 * @codeCoverageIgnore
	 */
	public function participants()
	{
		return $this->hasMany(Models::classname(Participant::class), 'thread_id', 'thread_id');
	}

	/**
	 * Recipients of this message.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function recipients()
	{
		return $this->participants()->where('user_id', '!=', $this->user_id);
	}

	/**
	 * Returns unread messages given the userId.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
	 * @param int $userId
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeUnreadForUser(Builder $query, $userId)
	{
		return $query->has('thread')
			->where('user_id', '!=', $userId)
			->whereHas('participants', function (Builder $query) use ($userId) {
				$query->where('user_id', $userId)
					->whereNull('deleted_at')
					->where(function (Builder $q) {
						$q->where('last_read', '<', $this->getConnection()->raw($this->getConnection()->getTablePrefix() . $this->getTable() . '.created_at'))
							->orWhereNull('last_read');
					});
			});
	}
}
